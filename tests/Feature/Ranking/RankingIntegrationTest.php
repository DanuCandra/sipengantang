<?php

namespace Tests\Feature\Ranking;

use App\Domain\Ranking\Actions\FinalizeClassResultAction;
use App\Domain\Ranking\Actions\RecalculateProvisionalRankingAction;
use App\Domain\Ranking\Services\TopsisCalculator;
use App\Enums\EvaluationStatus;
use App\Enums\RegistrationStatus;
use App\Jobs\RecalculateProvisionalRanking;
use App\Models\RankingRun;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Queue;
use LogicException;
use Tests\Concerns\CreatesRankingFixtures;
use Tests\TestCase;

class RankingIntegrationTest extends TestCase
{
    use CreatesRankingFixtures, LazilyRefreshDatabase;

    public function test_score_submission_and_dq_commit_then_enqueue_recalculation_without_reverb(): void
    {
        $f = $this->rankingFixture();
        $evaluation = $f['first']->judgeEvaluations()->where('judge_id', $f['judges'][0]->id)->sole();
        $evaluation->update(['status' => EvaluationStatus::Draft, 'submitted_at' => null]);
        Queue::fake([RecalculateProvisionalRanking::class]);

        $this->actingAs($f['judges'][0])->post(route('judge.evaluations.submit', [$f['class'], $f['first']]), ['scores' => $this->scoresFor($f['criteria'], 8)])->assertSessionHasNoErrors();
        $this->assertSame(EvaluationStatus::Submitted, $evaluation->fresh()->status);
        Queue::assertPushed(RecalculateProvisionalRanking::class, fn ($job) => $job->competitionClassId === $f['class']->id);
        (new RecalculateProvisionalRanking($f['class']->id))->handle(app(RecalculateProvisionalRankingAction::class));
        $this->assertSame(2, RankingRun::query()->latest('id')->first()->results()->count());

        $this->post(route('judge.disqualifications.store', [$f['class'], $f['first']]), ['reason_code' => 'LATE_HANGING', 'note' => 'Terlambat di lokasi.', 'confirm_disqualification' => 1])->assertSessionHasNoErrors();
        $this->assertSame(RegistrationStatus::Disqualified, $f['first']->fresh()->status);
        // One pending unique class job coalesces both changes, then reads fresh source.
        Queue::assertPushed(RecalculateProvisionalRanking::class, 1);
        (new RecalculateProvisionalRanking($f['class']->id))->handle(app(RecalculateProvisionalRankingAction::class));
        $this->assertSame($f['second']->id, RankingRun::query()->latest('id')->first()->results()->sole()->registration_id);
    }

    public function test_final_snapshot_can_reproduce_coefficients_from_archived_averages_and_criteria(): void
    {
        $f = $this->rankingFixture();
        $run = app(FinalizeClassResultAction::class)->execute($f['admin'], $f['class'])->fresh();
        $alternatives = [];
        foreach ($run->input_snapshot['registrations'] as $entry) {
            $alternatives[$entry['registration_id']] = $entry['averages'];
        }

        $reproduced = (new TopsisCalculator)->calculate($alternatives, $run->criteria_snapshot);

        foreach ($reproduced['results'] as $row) {
            $saved = $run->results()->where('registration_id', $row['registration_id'])->sole();
            $this->assertSame($saved->preference_value, $row['preference_key']);
            $this->assertSame($saved->positive_distance, number_format($row['positive_distance'], 10, '.', ''));
            $this->assertSame($saved->negative_distance, number_format($row['negative_distance'], 10, '.', ''));
        }
    }

    public function test_final_snapshots_reject_model_update_and_delete(): void
    {
        $f = $this->rankingFixture();
        $run = app(FinalizeClassResultAction::class)->execute($f['admin'], $f['class']);
        foreach ([fn () => $run->update(['type' => 'PROVISIONAL']), fn () => $run->delete(), fn () => $run->results()->orderBy('id')->firstOrFail()->update(['rank' => 99]), fn () => $run->results()->orderBy('id')->firstOrFail()->delete()] as $operation) {
            try {
                $operation();
                $this->fail('Immutable snapshot was changed.');
            } catch (LogicException $exception) {
                $this->assertStringContainsString('tidak dapat', $exception->getMessage());
            }
        }
        $this->assertDatabaseCount('ranking_runs', 1);
        $this->assertDatabaseCount('ranking_results', 2);
    }

    public function test_admin_page_escapes_snapshot_names_and_public_never_exposes_private_draw_notes(): void
    {
        $f = $this->rankingFixture(tied: true);
        $name = '<script>alert("bird")</script>';
        $note = '<script>alert("draw-private")</script>';
        $f['first']->bird()->first()->update(['name' => $name]);
        $run = app(RecalculateProvisionalRankingAction::class)->execute($f['class']);
        $ids = [$f['first']->id, $f['second']->id];
        sort($ids);
        $this->actingAs($f['admin'])->post(route('admin.events.classes.ranking.draw', [$f['event'], $f['class'], $run]), ['group_key' => hash('sha256', json_encode($ids)), 'ordered_registration_ids' => $ids, 'note' => $note, 'confirmation' => 1])->assertSessionHasNoErrors();
        $this->get(route('admin.events.classes.ranking', [$f['event'], $f['class']]))->assertSee($name)->assertDontSee($name, false)->assertSee($note)->assertDontSee($note, false);
        app(FinalizeClassResultAction::class)->execute($f['admin'], $f['class']);
        $this->get(route('events.classes.results', [$f['event'], $f['class']]))->assertSee($name)->assertDontSee($name, false)->assertDontSee($note)->assertDontSee('evaluations')->assertDontSee('judge_ids');
    }
}
