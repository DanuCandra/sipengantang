<?php

namespace Tests\Feature\Ranking;

use App\Domain\Ranking\Actions\FinalizeClassResultAction;
use App\Domain\Ranking\Actions\RecalculateProvisionalRankingAction;
use App\Enums\ActivityAction;
use App\Enums\CompetitionClassStatus;
use App\Enums\EvaluationStatus;
use App\Enums\RankingRunType;
use App\Enums\RegistrationStatus;
use App\Events\ResultFinalized;
use App\Models\JudgeAssignment;
use App\Models\RankingRun;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\Concerns\CreatesRankingFixtures;
use Tests\TestCase;

class RankingWorkflowTest extends TestCase
{
    use CreatesRankingFixtures, LazilyRefreshDatabase;

    public function test_provisional_averages_only_submitted_and_preserves_auditable_matrices(): void
    {
        $f = $this->rankingFixture();
        $evaluation = $f['second']->judgeEvaluations()->orderBy('judge_id')->first();
        $evaluation->update(['status' => EvaluationStatus::Draft, 'submitted_at' => null]);
        $evaluation->scores()->update(['score' => 1]);
        $firstEvaluation = $f['first']->judgeEvaluations()->orderBy('judge_id')->first();
        $firstEvaluation->scores()->update(['score' => 6]);

        $this->actingAs($f['admin'])->post(route('admin.events.classes.ranking.recalculate', [$f['event'], $f['class']]))->assertSessionHasNoErrors();

        $run = RankingRun::query()->sole();
        $this->assertSame(RankingRunType::Provisional, $run->type);
        $this->assertSame('TOPSIS', $run->algorithm);
        $this->assertSame('1.0.0', $run->algorithm_version);
        $this->assertSame(8, $run->results()->where('registration_id', $f['first']->id)->sole()->criterion_averages['C1']);
        $this->assertSame(7, $run->results()->where('registration_id', $f['second']->id)->sole()->criterion_averages['C1']);
        $this->assertArrayHasKey('weighted_matrix', $run->calculation_snapshot);
        $this->assertCount(3, $run->input_snapshot['registrations'][0]['evaluations']);
        $this->assertCount(2, $run->input_snapshot['registrations'][1]['evaluations']);
        $this->assertStringNotContainsString($f['first']->participant->email, json_encode($run->input_snapshot));
    }

    public function test_duplicate_recalculation_is_idempotent_and_never_mixes_classes_or_ineligible_entries(): void
    {
        $f = $this->rankingFixture();
        Registration::factory()->create(['status' => RegistrationStatus::Verified]);
        foreach ([RegistrationStatus::PendingPayment, RegistrationStatus::WaitingVerification, RegistrationStatus::PaymentRejected, RegistrationStatus::Cancelled, RegistrationStatus::Disqualified] as $status) {
            Registration::factory()->for($f['class'], 'competitionClass')->create(['status' => $status]);
        }

        $action = app(RecalculateProvisionalRankingAction::class);
        $run = $action->execute($f['class']);
        $again = $action->execute($f['class']);

        $this->assertSame($run->id, $again->id);
        $this->assertDatabaseCount('ranking_runs', 1);
        $this->assertSame([$f['first']->id, $f['second']->id], $run->results()->orderBy('registration_id')->pluck('registration_id')->all());
    }

    public function test_finalization_recalculates_raw_scores_and_keeps_old_provisional_snapshot(): void
    {
        $f = $this->rankingFixture();
        $old = app(RecalculateProvisionalRankingAction::class)->execute($f['class']);
        $oldSnapshot = $old->fresh()->toArray();
        $f['first']->update(['status' => RegistrationStatus::Disqualified, 'disqualified_at' => now()]);
        Event::fake([ResultFinalized::class]);
        $this->travelTo(now()->startOfSecond());

        $this->actingAs($f['admin'])->post(route('admin.events.classes.ranking.finalize', [$f['event'], $f['class']]), ['confirmation' => 'FINALISASI'])->assertSessionHasNoErrors();

        $final = RankingRun::query()->where('type', RankingRunType::Final)->sole();
        $this->assertSame($f['second']->id, $final->results()->sole()->registration_id);
        $this->assertSame(1, $final->results()->sole()->rank);
        $this->assertSame('0.5000000000', $final->results()->sole()->preference_value);
        $this->assertSame($oldSnapshot, $old->fresh()->toArray());
        $this->assertSame(CompetitionClassStatus::Finalized, $f['class']->fresh()->status);
        $this->assertTrue($final->finalized_at->equalTo(now()));
        $this->assertDatabaseHas('activity_logs', ['action' => ActivityAction::ResultFinalized->value, 'user_id' => $f['admin']->id, 'subject_id' => $final->id]);
        Event::assertDispatched(ResultFinalized::class, fn ($event) => $event->rankingRunId === $final->id && $event->competitionClassId === $f['class']->id);
        $this->assertDatabaseCount('judge_scores', 30);
    }

    public function test_minimum_three_and_all_assigned_judges_are_required_to_finalize(): void
    {
        $f = $this->rankingFixture();
        $absent = User::factory()->judge()->create();
        $assignment = JudgeAssignment::factory()->for($f['class'], 'competitionClass')->for($absent, 'judge')->create();
        $url = route('admin.events.classes.ranking.finalize', [$f['event'], $f['class']]);
        $this->actingAs($f['admin'])->post($url, ['confirmation' => 'FINALISASI'])->assertSessionHasErrors('ranking');
        $assignment->update(['replaced_at' => now()]);
        $other = $f['class']->judgeAssignments()->active()->where('judge_id', $f['judges'][0]->id)->sole();
        $other->update(['replaced_at' => now()]);
        $this->post($url, ['confirmation' => 'FINALISASI'])->assertSessionHasErrors('ranking');
        $this->assertDatabaseCount('ranking_runs', 0);
        $this->assertSame(CompetitionClassStatus::Ongoing, $f['class']->fresh()->status);
    }

    public function test_unscored_verified_participant_blocks_finalization_and_all_dq_has_no_winner(): void
    {
        $f = $this->rankingFixture();
        $unscored = Registration::factory()->for($f['class'], 'competitionClass')->create(['status' => RegistrationStatus::Verified]);
        $url = route('admin.events.classes.ranking.finalize', [$f['event'], $f['class']]);
        $this->actingAs($f['admin'])->post($url, ['confirmation' => 'FINALISASI'])->assertSessionHasErrors('ranking');
        foreach ([$f['first'], $f['second'], $unscored] as $registration) {
            $registration->update(['status' => RegistrationStatus::Disqualified]);
        }
        $run = app(RecalculateProvisionalRankingAction::class)->execute($f['class']);
        $this->assertSame(0, $run->results()->count());
        $this->post($url, ['confirmation' => 'FINALISASI'])->assertSessionHasErrors('ranking');
        $this->assertSame(0, RankingRun::query()->where('type', RankingRunType::Final)->count());
    }

    public function test_invalid_weight_or_corrupt_submitted_scores_are_rejected_without_partial_run(): void
    {
        $f = $this->rankingFixture();
        $f['criteria'][0]->update(['weight' => '0.500000']);
        $url = route('admin.events.classes.ranking.recalculate', [$f['event'], $f['class']]);
        $this->actingAs($f['admin'])->post($url)->assertSessionHasErrors('ranking');
        $f['criteria'][0]->update(['weight' => '0.400000']);
        $evaluation = $f['first']->judgeEvaluations()->first();
        $evaluation->scores()->first()->delete();
        $this->post($url)->assertSessionHasErrors('ranking');
        $this->assertDatabaseCount('ranking_runs', 0);
        $this->assertDatabaseCount('ranking_results', 0);
    }

    public function test_repeated_finalize_and_late_job_cannot_create_another_official_result(): void
    {
        $f = $this->rankingFixture();
        $url = route('admin.events.classes.ranking.finalize', [$f['event'], $f['class']]);
        $this->actingAs($f['admin'])->post($url, ['confirmation' => 'FINALISASI'])->assertSessionHasNoErrors();
        $snapshot = RankingRun::query()->sole()->toArray();
        $this->post($url, ['confirmation' => 'FINALISASI'])->assertSessionHasErrors('ranking');
        $this->assertNull(app(RecalculateProvisionalRankingAction::class)->execute($f['class']));
        $this->assertSame($snapshot, RankingRun::query()->sole()->toArray());
    }

    public function test_rollback_restores_final_run_results_class_and_audit_without_event(): void
    {
        $f = $this->rankingFixture();
        Event::fake([ResultFinalized::class]);
        try {
            DB::transaction(function () use ($f): void {
                app(FinalizeClassResultAction::class)->execute($f['admin'], $f['class']);
                Event::assertNotDispatched(ResultFinalized::class);
                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException $e) {
            $this->assertSame('rollback', $e->getMessage());
        }
        $this->assertDatabaseCount('ranking_runs', 0);
        $this->assertDatabaseCount('ranking_results', 0);
        $this->assertDatabaseCount('activity_logs', 0);
        $this->assertSame(CompetitionClassStatus::Ongoing, $f['class']->fresh()->status);
        Event::assertNotDispatched(ResultFinalized::class);
    }
}
