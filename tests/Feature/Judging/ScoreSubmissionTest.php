<?php

namespace Tests\Feature\Judging;

use App\Domain\Judging\Actions\SaveEvaluationAction;
use App\Enums\ActivityAction;
use App\Enums\CompetitionClassStatus;
use App\Enums\EvaluationStatus;
use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Events\JudgeScoreSubmitted;
use App\Models\ActivityLog;
use App\Models\CompetitionClassCriterion;
use App\Models\JudgeEvaluation;
use App\Models\JudgeScore;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Concerns\CreatesJudgingFixtures;
use Tests\TestCase;

class ScoreSubmissionTest extends TestCase
{
    use CreatesJudgingFixtures, LazilyRefreshDatabase;

    public function test_assigned_judge_can_save_incomplete_draft_and_continue_it_without_creating_duplicate(): void
    {
        [$judge, , $competitionClass, $registration, $criteria] = $this->judgingFixture();
        Event::fake([JudgeScoreSubmitted::class]);
        $this->actingAs($judge)->post(route('judge.evaluations.draft', [$competitionClass, $registration]), ['scores' => [$criteria[0]->id => 9]])->assertRedirect();

        $evaluation = JudgeEvaluation::query()->sole();
        $this->assertSame(EvaluationStatus::Draft, $evaluation->status);
        $this->assertNull($evaluation->submitted_at);
        $this->assertDatabaseCount('judge_scores', 1);
        $this->assertDatabaseHas('judge_scores', ['judge_evaluation_id' => $evaluation->id, 'criterion_id' => $criteria[0]->id, 'score' => 9]);

        $this->actingAs($judge)->post(route('judge.evaluations.draft', [$competitionClass, $registration]), ['scores' => $this->scoresFor($criteria)])->assertRedirect();

        $this->assertDatabaseCount('judge_evaluations', 1);
        $this->assertDatabaseCount('judge_scores', 5);
        $this->assertDatabaseHas('activity_logs', ['action' => ActivityAction::ScoreDraftSaved->value, 'user_id' => $judge->id]);
        Event::assertNotDispatched(JudgeScoreSubmitted::class);
        $this->actingAs($judge)->get(route('judge.evaluations.edit', [$competitionClass, $registration]))->assertSee('Simpan draft');
    }

    public function test_submit_persists_all_class_scores_and_server_owned_identity_then_dispatches_event(): void
    {
        [$judge, , $competitionClass, $registration, $criteria] = $this->judgingFixture();
        $this->travelTo(now()->startOfSecond());
        Event::fake([JudgeScoreSubmitted::class]);

        $this->actingAs($judge)->post(route('judge.evaluations.submit', [$competitionClass, $registration]), [
            'scores' => $this->scoresFor($criteria), 'judge_id' => 99999, 'registration_id' => 99999, 'status' => EvaluationStatus::Draft->value,
        ])->assertRedirect(route('judge.evaluations.edit', [$competitionClass, $registration]));

        $evaluation = JudgeEvaluation::query()->sole();
        $this->assertSame($judge->id, $evaluation->judge_id);
        $this->assertSame($registration->id, $evaluation->registration_id);
        $this->assertSame(EvaluationStatus::Submitted, $evaluation->status);
        $this->assertTrue($evaluation->submitted_at->equalTo(now()));
        $this->assertDatabaseCount('judge_scores', 5);
        $this->assertDatabaseHas('activity_logs', ['action' => ActivityAction::ScoreSubmitted->value, 'subject_id' => $evaluation->id]);
        Event::assertDispatched(JudgeScoreSubmitted::class, fn ($event) => $event->evaluationId === $evaluation->id && $event->registrationId === $registration->id && $event->competitionClassId === $competitionClass->id);
        $this->actingAs($judge)->get(route('judge.evaluations.edit', [$competitionClass, $registration]))
            ->assertSee('Nilai terkunci')->assertDontSee('name="scores[', false)->assertDontSee('Simpan draft');
    }

    public function test_incomplete_submission_is_rejected_without_changing_saved_draft(): void
    {
        [$judge, , $competitionClass, $registration, $criteria] = $this->judgingFixture();
        $this->actingAs($judge)->post(route('judge.evaluations.draft', [$competitionClass, $registration]), ['scores' => [$criteria[0]->id => 7]])->assertRedirect();
        Event::fake([JudgeScoreSubmitted::class]);

        $this->actingAs($judge)->post(route('judge.evaluations.submit', [$competitionClass, $registration]), ['scores' => [$criteria[0]->id => 9]])
            ->assertSessionHasErrors(['scores.'.$criteria[1]->id]);

        $this->assertSame(EvaluationStatus::Draft, JudgeEvaluation::query()->sole()->status);
        $this->assertDatabaseHas('judge_scores', ['criterion_id' => $criteria[0]->id, 'score' => 7]);
        $this->assertDatabaseCount('judge_scores', 1);
        Event::assertNotDispatched(JudgeScoreSubmitted::class);
    }

    #[DataProvider('invalidScores')]
    public function test_score_values_outside_integer_range_are_rejected(mixed $score): void
    {
        [$judge, , $competitionClass, $registration, $criteria] = $this->judgingFixture();
        $values = $this->scoresFor($criteria);
        $values[$criteria[0]->id] = $score;

        $this->actingAs($judge)->post(route('judge.evaluations.submit', [$competitionClass, $registration]), ['scores' => $values])->assertSessionHasErrors('scores.'.$criteria[0]->id);

        $this->assertDatabaseCount('judge_evaluations', 0);
        $this->assertDatabaseCount('judge_scores', 0);
    }

    public static function invalidScores(): array
    {
        return ['below minimum' => [0], 'above maximum' => [11], 'fraction' => [8.5], 'text' => ['excellent'], 'nested' => [['score' => 8]]];
    }

    public function test_criterion_from_another_class_is_rejected(): void
    {
        [$judge, , $competitionClass, $registration, $criteria] = $this->judgingFixture();
        $otherCriterion = CompetitionClassCriterion::factory()->create();
        $values = $this->scoresFor($criteria);
        $values[$otherCriterion->id] = 9;

        $this->actingAs($judge)->post(route('judge.evaluations.submit', [$competitionClass, $registration]), ['scores' => $values])
            ->assertSessionHasErrors(['scores' => 'Terdapat kriteria yang bukan milik kelas ini.']);

        $this->assertDatabaseCount('judge_evaluations', 0);
    }

    public function test_repeated_submit_and_late_draft_cannot_overwrite_submitted_scores(): void
    {
        [$judge, , $competitionClass, $registration, $criteria] = $this->judgingFixture();
        $this->actingAs($judge)->post(route('judge.evaluations.submit', [$competitionClass, $registration]), ['scores' => $this->scoresFor($criteria, 9)])->assertRedirect();
        $originalIds = JudgeScore::query()->orderBy('id')->pluck('id')->all();

        $this->actingAs($judge)->post(route('judge.evaluations.submit', [$competitionClass, $registration]), ['scores' => $this->scoresFor($criteria, 1)])->assertSessionHasErrors('judging');
        $this->actingAs($judge)->post(route('judge.evaluations.draft', [$competitionClass, $registration]), ['scores' => $this->scoresFor($criteria, 1)])->assertSessionHasErrors('judging');

        $this->assertDatabaseCount('judge_evaluations', 1);
        $this->assertDatabaseCount('judge_scores', 5);
        $this->assertSame($originalIds, JudgeScore::query()->orderBy('id')->pluck('id')->all());
        $this->assertSame([9], JudgeScore::query()->distinct()->pluck('score')->all());
        $this->assertSame(1, ActivityLog::query()->where('action', ActivityAction::ScoreSubmitted)->count());
    }

    #[DataProvider('ineligibleRegistrations')]
    public function test_unverified_or_disqualified_registration_cannot_be_scored(RegistrationStatus $status): void
    {
        [$judge, , $competitionClass, $registration, $criteria] = $this->judgingFixture();
        $registration->update(['status' => $status]);

        $this->actingAs($judge)->post(route('judge.evaluations.submit', [$competitionClass, $registration]), ['scores' => $this->scoresFor($criteria)])->assertSessionHasErrors('judging');

        $this->assertDatabaseCount('judge_evaluations', 0);
    }

    public static function ineligibleRegistrations(): array
    {
        return array_map(fn ($status) => [$status], [RegistrationStatus::PendingPayment, RegistrationStatus::WaitingVerification, RegistrationStatus::PaymentRejected, RegistrationStatus::Cancelled, RegistrationStatus::Disqualified]);
    }

    #[DataProvider('closedClasses')]
    public function test_scoring_is_blocked_outside_ongoing_class(CompetitionClassStatus $status): void
    {
        [$judge, , $competitionClass, $registration, $criteria] = $this->judgingFixture();
        $competitionClass->update(['status' => $status]);

        $this->actingAs($judge)->post(route('judge.evaluations.draft', [$competitionClass, $registration]), ['scores' => $this->scoresFor($criteria)])->assertSessionHasErrors('judging');

        $this->assertDatabaseCount('judge_evaluations', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public static function closedClasses(): array
    {
        return array_map(fn ($status) => [$status], [CompetitionClassStatus::Upcoming, CompetitionClassStatus::JudgingCompleted, CompetitionClassStatus::Finalized, CompetitionClassStatus::Cancelled]);
    }

    public function test_rollback_restores_evaluation_scores_and_audit_and_defers_event(): void
    {
        [$judge, , , $registration, $criteria] = $this->judgingFixture();
        Event::fake([JudgeScoreSubmitted::class]);
        try {
            DB::transaction(function () use ($judge, $registration, $criteria): void {
                $this->app->make(SaveEvaluationAction::class)->execute($judge, $registration, $this->scoresFor($criteria), EvaluationStatus::Submitted);
                Event::assertNotDispatched(JudgeScoreSubmitted::class);
                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException $exception) {
            $this->assertSame('rollback', $exception->getMessage());
        }

        $this->assertDatabaseCount('judge_evaluations', 0);
        $this->assertDatabaseCount('judge_scores', 0);
        $this->assertDatabaseCount('activity_logs', 0);
        Event::assertNotDispatched(JudgeScoreSubmitted::class);
    }

    #[DataProvider('closedEvents')]
    public function test_scoring_is_blocked_when_event_is_not_ongoing(EventStatus $status): void
    {
        [$judge, $event, $competitionClass, $registration, $criteria] = $this->judgingFixture();
        $event->update(['status' => $status]);

        $this->actingAs($judge)->post(route('judge.evaluations.submit', [$competitionClass, $registration]), ['scores' => $this->scoresFor($criteria)])->assertSessionHasErrors('judging');

        $this->assertDatabaseCount('judge_evaluations', 0);
    }

    public static function closedEvents(): array
    {
        return array_map(fn ($status) => [$status], [EventStatus::Draft, EventStatus::Upcoming, EventStatus::Completed, EventStatus::Cancelled]);
    }

    public function test_unpublished_event_and_incomplete_class_criteria_cannot_accept_scores(): void
    {
        [$judge, $event, $competitionClass, $registration, $criteria] = $this->judgingFixture();
        $event->update(['published_at' => null]);
        $this->actingAs($judge)->post(route('judge.evaluations.draft', [$competitionClass, $registration]), ['scores' => $this->scoresFor($criteria)])->assertSessionHasErrors('judging');
        $event->update(['published_at' => now()]);
        $criteria->last()->delete();

        $this->actingAs($judge)->post(route('judge.evaluations.submit', [$competitionClass, $registration]), ['scores' => []])->assertSessionHasErrors('judging');

        $this->assertDatabaseCount('judge_evaluations', 0);
    }
}
