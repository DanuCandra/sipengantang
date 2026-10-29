<?php

namespace Tests\Feature\Judging;

use App\Enums\EvaluationStatus;
use App\Enums\RegistrationStatus;
use App\Models\CompetitionClass;
use App\Models\JudgeAssignment;
use App\Models\JudgeEvaluation;
use App\Models\JudgeScore;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\Concerns\CreatesJudgingFixtures;
use Tests\TestCase;

class JudgingAccessTest extends TestCase
{
    use CreatesJudgingFixtures, LazilyRefreshDatabase;

    public function test_guest_cannot_open_assignment_or_submit_score(): void
    {
        [, , $competitionClass, $registration] = $this->judgingFixture();

        $this->get(route('judge.classes.index'))->assertRedirect(route('login'));
        $this->post(route('judge.evaluations.submit', [$competitionClass, $registration]))->assertRedirect(route('login'));
    }

    public function test_unassigned_judge_cannot_view_or_score_class(): void
    {
        [, , $competitionClass, $registration, $criteria] = $this->judgingFixture();
        $judge = User::factory()->judge()->create();

        $this->actingAs($judge)->get(route('judge.classes.show', $competitionClass))->assertForbidden();
        $this->actingAs($judge)->get(route('judge.evaluations.edit', [$competitionClass, $registration]))->assertForbidden();
        $this->actingAs($judge)->post(route('judge.evaluations.submit', [$competitionClass, $registration]), ['scores' => $this->scoresFor($criteria)])->assertForbidden();
        $this->assertDatabaseCount('judge_evaluations', 0);
    }

    public function test_judge_dashboard_lists_only_assigned_classes_and_eligible_participants(): void
    {
        [$judge, , $competitionClass, $registration] = $this->judgingFixture();
        $other = CompetitionClass::factory()->create(['name' => 'Kelas rahasia lain']);
        $unverified = Registration::factory()->for($competitionClass)->create(['registration_number' => 'REG-PRIVATE-UNPAID']);

        $this->actingAs($judge)->get(route('judge.dashboard'))->assertSee($competitionClass->name)->assertDontSee($other->name);
        $this->actingAs($judge)->get(route('judge.classes.show', $competitionClass))
            ->assertSee($registration->registration_number)->assertDontSee($unverified->registration_number);
        $this->actingAs($judge)->get(route('judge.evaluations.edit', [$competitionClass, $unverified]))->assertForbidden();
    }

    public function test_cross_class_registration_is_rejected_even_when_judge_is_assigned_to_both(): void
    {
        [$judge, , $competitionClass] = $this->judgingFixture();
        $otherRegistration = Registration::factory()->create(['status' => RegistrationStatus::Verified]);
        JudgeAssignment::factory()->create(['competition_class_id' => $otherRegistration->competition_class_id, 'judge_id' => $judge->id]);

        $this->actingAs($judge)->get(route('judge.evaluations.edit', [$competitionClass, $otherRegistration]))->assertNotFound();
        $this->actingAs($judge)->post(route('judge.evaluations.draft', [$competitionClass, $otherRegistration]), ['scores' => []])->assertForbidden();
        $this->assertDatabaseCount('judge_evaluations', 0);
    }

    public function test_scoring_page_loads_only_current_judges_scores_and_no_participant_private_contacts(): void
    {
        [$judge, , $competitionClass, $registration, $criteria] = $this->judgingFixture();
        $other = User::factory()->judge()->create(['email' => 'otherjudge@example.test']);
        $otherEvaluation = JudgeEvaluation::factory()->for($registration)->for($other, 'judge')->create();
        JudgeScore::factory()->create(['judge_evaluation_id' => $otherEvaluation->id, 'criterion_id' => $criteria[0]->id, 'score' => 10]);
        $own = JudgeEvaluation::factory()->for($registration)->for($judge, 'judge')->create();
        JudgeScore::factory()->create(['judge_evaluation_id' => $own->id, 'criterion_id' => $criteria[0]->id, 'score' => 7]);

        $response = $this->actingAs($judge)->get(route('judge.evaluations.edit', [$competitionClass, $registration]));

        $response->assertSee('Simpan draft')->assertDontSee('otherjudge@example.test');
        $this->assertSame($own->id, $response->viewData('evaluation')->id);
        $this->assertSame([$criteria[0]->id => 7], $response->viewData('scores'));
        $this->assertSame(['id', 'name'], array_keys($response->viewData('registration')->participant->getAttributes()));
        $listing = $this->actingAs($judge)->get(route('judge.classes.show', $competitionClass));
        $this->assertSame([$own->id], $listing->viewData('registrations')->first()->judgeEvaluations->modelKeys());
    }

    public function test_disqualification_blocks_further_scoring_and_preserves_submitted_history(): void
    {
        [$judge, , $competitionClass, $registration, $criteria] = $this->judgingFixture();
        $this->actingAs($judge)->post(route('judge.evaluations.submit', [$competitionClass, $registration]), ['scores' => $this->scoresFor($criteria)])->assertRedirect();
        $registration->update(['status' => RegistrationStatus::Disqualified, 'disqualified_at' => now()]);

        $this->actingAs($judge)->post(route('judge.evaluations.draft', [$competitionClass, $registration]), ['scores' => $this->scoresFor($criteria, 1)])->assertSessionHasErrors('judging');
        $response = $this->actingAs($judge)->get(route('judge.evaluations.edit', [$competitionClass, $registration]));

        $response->assertSee('Nilai terkunci')->assertDontSee('Simpan draft');
        $this->assertSame(EvaluationStatus::Submitted, JudgeEvaluation::query()->sole()->status);
        $this->assertDatabaseCount('judge_scores', 5);
        $this->assertSame([8], JudgeScore::query()->distinct()->pluck('score')->all());
    }

    public function test_role_and_evaluation_ownership_policy_matrix(): void
    {
        [$judge, , $competitionClass, $registration] = $this->judgingFixture();
        $own = JudgeEvaluation::factory()->for($registration)->for($judge, 'judge')->create();
        $otherJudge = User::factory()->judge()->create();
        JudgeAssignment::factory()->for($competitionClass)->for($otherJudge, 'judge')->create();
        $admin = User::factory()->admin()->create();
        $participant = $registration->participant()->firstOrFail();

        foreach ([[$judge, true, true], [$otherJudge, true, false], [$admin, false, false], [$participant, false, false]] as [$user, $canScore, $canRead]) {
            $this->assertSame($canScore, Gate::forUser($user)->allows('score', [JudgeEvaluation::class, $registration]));
            $this->assertSame($canRead, Gate::forUser($user)->allows('view', $own));
        }
        $this->actingAs($participant)->get(route('judge.classes.index'))->assertForbidden();
    }
}
