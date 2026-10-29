<?php

namespace Tests\Feature\Ranking;

use App\Domain\Judging\Services\JudgingProgressService;
use App\Enums\ActivityAction;
use App\Models\JudgeAssignment;
use App\Models\JudgeEvaluation;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesRankingFixtures;
use Tests\TestCase;

class AbsentJudgeReplacementTest extends TestCase
{
    use CreatesRankingFixtures, LazilyRefreshDatabase;

    public function test_absent_judge_is_replaced_without_deleting_drafts_or_reducing_required_judges(): void
    {
        $f = $this->rankingFixture();
        $absent = User::factory()->judge()->create();
        $assignment = JudgeAssignment::factory()->for($f['class'], 'competitionClass')->for($absent, 'judge')->create();
        $draft = JudgeEvaluation::factory()->for($f['first'], 'registration')->for($absent, 'judge')->create();
        $draft->scores()->create(['criterion_id' => $f['criteria'][0]->id, 'score' => 4]);
        $replacement = User::factory()->participant()->create();

        $this->actingAs($f['admin'])->post(route('admin.events.classes.judges.replace', [$f['event'], $f['class'], $assignment]), ['replacement_email' => $replacement->email, 'replacement_note' => 'Juri berhalangan hadir.'])->assertSessionHasNoErrors();

        $this->assertNotNull($assignment->fresh()->replaced_at);
        $this->assertSame($replacement->id, $assignment->fresh()->replacement->judge_id);
        $this->assertSame(4, app(JudgingProgressService::class)->summary($f['class'])['assigned']);
        $this->assertDatabaseHas('judge_scores', ['judge_evaluation_id' => $draft->id, 'score' => 4]);
        $this->assertDatabaseHas('activity_logs', ['action' => ActivityAction::JudgeReplaced->value, 'user_id' => $f['admin']->id]);
        $this->actingAs($absent)->get(route('judge.classes.show', $f['class']))->assertForbidden();
        $this->post(route('judge.evaluations.draft', [$f['class'], $f['first']]), ['scores' => []])->assertForbidden();
        $this->actingAs($replacement)->get(route('judge.classes.show', $f['class']))->assertOk();
        $this->actingAs($f['admin'])->get(route('admin.events.classes.judges.index', [$f['event'], $f['class']]))->assertSee('Penugasan diganti');
    }

    public function test_submitted_judge_cannot_be_replaced_or_have_scores_silently_excluded(): void
    {
        $f = $this->rankingFixture();
        $assignment = $f['class']->judgeAssignments()->where('judge_id', $f['judges'][0]->id)->sole();
        $this->actingAs($f['admin'])->post(route('admin.events.classes.judges.replace', [$f['event'], $f['class'], $assignment]), ['replacement_email' => User::factory()->judge()->create()->email, 'replacement_note' => 'Attempt'])->assertSessionHasErrors('replacement');
        $this->assertNull($assignment->fresh()->replaced_at);
        $this->assertDatabaseCount('judge_scores', 30);
        $this->assertDatabaseCount('judge_assignments', 3);
    }

    public function test_replacement_authorization_nested_scope_inactive_account_and_required_reason(): void
    {
        $f = $this->rankingFixture();
        $absent = User::factory()->judge()->create();
        $assignment = JudgeAssignment::factory()->for($f['class'], 'competitionClass')->for($absent, 'judge')->create();
        $url = route('admin.events.classes.judges.replace', [$f['event'], $f['class'], $assignment]);
        $payload = ['replacement_email' => User::factory()->inactive()->judge()->create()->email, 'replacement_note' => 'Absent'];
        $this->actingAs(User::factory()->admin()->create())->post($url, $payload)->assertForbidden();
        $this->actingAs($f['admin'])->post($url, $payload)->assertSessionHasErrors('replacement');
        $this->post($url, ['replacement_email' => $absent->email])->assertSessionHasErrors('replacement_note');
        $other = JudgeAssignment::factory()->create();
        $this->post(route('admin.events.classes.judges.replace', [$f['event'], $f['class'], $other]), $payload)->assertNotFound();
        $this->assertNull($assignment->fresh()->replaced_at);
    }
}
