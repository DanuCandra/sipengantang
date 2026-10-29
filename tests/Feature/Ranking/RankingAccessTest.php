<?php

namespace Tests\Feature\Ranking;

use App\Domain\Ranking\Actions\FinalizeClassResultAction;
use App\Domain\Ranking\Actions\RecalculateProvisionalRankingAction;
use App\Domain\Ranking\Services\TopsisCalculator;
use App\Enums\ActivityAction;
use App\Enums\EventStatus;
use App\Models\Prize;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\Concerns\CreatesRankingFixtures;
use Tests\TestCase;

class RankingAccessTest extends TestCase
{
    use CreatesRankingFixtures, LazilyRefreshDatabase;

    public function test_guest_unrelated_admin_judge_and_participant_cannot_view_or_change_internal_ranking(): void
    {
        $f = $this->rankingFixture();
        $page = route('admin.events.classes.ranking', [$f['event'], $f['class']]);
        $write = route('admin.events.classes.ranking.finalize', [$f['event'], $f['class']]);
        $this->get($page)->assertRedirect(route('login'));
        $run = app(RecalculateProvisionalRankingAction::class)->execute($f['class']);
        foreach ([User::factory()->admin()->create(), $f['judges'][0], $f['first']->participant] as $user) {
            $this->actingAs($user)->get($page)->assertForbidden();
            $this->post($write, ['confirmation' => 'FINALISASI'])->assertForbidden();
            $this->assertFalse(Gate::forUser($user)->allows('view', $run));
        }
        $this->assertTrue(Gate::forUser($f['admin'])->allows('view', $run));
        $this->assertTrue(Gate::forUser($f['event']->owner)->allows('view', $run));
    }

    public function test_cross_event_class_and_ranking_run_are_rejected(): void
    {
        $f = $this->rankingFixture(tied: true);
        $other = $this->rankingFixture(tied: true);
        $run = app(RecalculateProvisionalRankingAction::class)->execute($other['class']);
        $this->actingAs($f['admin'])->get(route('admin.events.classes.ranking', [$f['event'], $other['class']]))->assertNotFound();
        $this->post(route('admin.events.classes.ranking.recalculate', [$f['event'], $other['class']]))->assertForbidden();
        $this->post(route('admin.events.classes.ranking.draw', [$f['event'], $f['class'], $run]), ['group_key' => str_repeat('a', 64), 'ordered_registration_ids' => [$f['first']->id, $f['second']->id], 'note' => 'Test', 'confirmation' => 1])->assertNotFound();
    }

    public function test_public_cannot_view_provisional_and_final_page_reads_stored_snapshot_without_calculating(): void
    {
        $f = $this->rankingFixture();
        $this->get(route('events.classes.results', [$f['event'], $f['class']]))->assertNotFound();
        $final = app(FinalizeClassResultAction::class)->execute($f['admin'], $f['class']);
        $originalName = $final->results()->orderBy('rank')->first()->entry_snapshot['bird_name'];
        $f['first']->bird()->first()->update(['name' => 'MUTATED-LIVE-BIRD']);
        $f['first']->participant()->first()->update(['email' => 'PRIVATE-CONTACT@example.test']);
        $this->app->bind(TopsisCalculator::class, fn () => throw new \RuntimeException('Public request must not calculate'));

        $response = $this->get(route('events.classes.results', [$f['event'], $f['class']]));

        $response->assertOk()->assertSee('Hasil resmi')->assertSee($originalName)->assertDontSee('MUTATED-LIVE-BIRD')->assertDontSee('PRIVATE-CONTACT@example.test');
        $this->assertSame(['class_name', 'event_name', 'prizes', 'finalized_at'], array_keys($response->viewData('info')));
        $this->assertFalse(array_key_exists('criterion_averages', $response->viewData('results')->first()->getAttributes()));
        $this->assertDatabaseCount('ranking_runs', 1);
    }

    public function test_finalized_class_blocks_scoring_dq_assignment_class_update_and_prize_changes(): void
    {
        $f = $this->rankingFixture();
        $prize = Prize::factory()->for($f['class'], 'competitionClass')->create();
        app(FinalizeClassResultAction::class)->execute($f['admin'], $f['class']);
        $this->actingAs($f['judges'][0])->post(route('judge.warnings.store', [$f['class'], $f['first']]), ['reason_code' => 'SHOUTING'])->assertSessionHasErrors('violation');
        $this->post(route('judge.evaluations.draft', [$f['class'], $f['first']]), ['scores' => []])->assertSessionHasErrors('judging');
        $this->actingAs($f['admin'])->post(route('admin.events.classes.judges.store', [$f['event'], $f['class']]), ['email' => User::factory()->judge()->create()->email])->assertSessionHasErrors('judging');
        $this->delete(route('admin.events.classes.prizes.destroy', [$f['event'], $f['class'], $prize]))->assertSessionHasErrors('ranking');
        $this->post(route('admin.events.classes.prizes.store', [$f['event'], $f['class']]), ['rank' => 2, 'type' => 'CASH', 'title' => 'Forbidden prize', 'cash_amount' => 10000])->assertSessionHasErrors('ranking');
        $this->assertDatabaseHas('prizes', ['id' => $prize->id]);
        $this->put(route('admin.events.classes.update', [$f['event'], $f['class']]), [
            'name' => 'Forbidden rename', 'bird_type' => $f['class']->bird_type, 'registration_fee' => 150000, 'start_at' => $f['class']->start_at->toDateTimeString(),
        ])->assertSessionHasErrors('ranking');
        $this->assertNotSame('Forbidden rename', $f['class']->fresh()->name);
        $assignment = $f['class']->judgeAssignments()->orderBy('id')->first();
        $this->post(route('admin.events.classes.judges.replace', [$f['event'], $f['class'], $assignment]), ['replacement_email' => User::factory()->judge()->create()->email, 'replacement_note' => 'Late replacement'])->assertSessionHasErrors('judging');
        $this->get(route('admin.events.classes.show', [$f['event'], $f['class']]))->assertSee('Kelas sudah final.')->assertDontSee('Tambah hadiah')->assertDontSee('Edit kelas');
        $this->get(route('admin.events.classes.ranking', [$f['event'], $f['class']]))->assertOk()->assertSee('FINAL')->assertDontSee('name="confirmation"', false);
    }

    public function test_invalid_confirmation_cannot_finalize_and_event_requires_all_classes_final(): void
    {
        $f = $this->rankingFixture();
        $this->actingAs($f['admin'])->post(route('admin.events.classes.ranking.finalize', [$f['event'], $f['class']]), ['confirmation' => 'yes'])->assertSessionHasErrors('confirmation');
        $this->post(route('admin.events.complete', $f['event']), ['confirmation' => 'SELESAI'])->assertSessionHasErrors('event');
        app(FinalizeClassResultAction::class)->execute($f['admin'], $f['class']);
        $this->post(route('admin.events.complete', $f['event']), ['confirmation' => 'SELESAI'])->assertSessionHasNoErrors();
        $this->assertSame(EventStatus::Completed, $f['event']->fresh()->status);
        $this->assertDatabaseHas('activity_logs', ['action' => ActivityAction::EventCompleted->value]);
        $this->get(route('events.classes.results', [$f['event'], $f['class']]))->assertOk();
        $this->post(route('admin.events.classes.store', $f['event']), ['name' => 'Late class', 'bird_type' => 'Kacer', 'registration_fee' => 150000, 'start_at' => now()->addDay()->toDateTimeString()])->assertSessionHasErrors('event');
    }
}
