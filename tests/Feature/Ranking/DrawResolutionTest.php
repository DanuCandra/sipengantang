<?php

namespace Tests\Feature\Ranking;

use App\Domain\Ranking\Actions\RecalculateProvisionalRankingAction;
use App\Enums\ActivityAction;
use App\Enums\RankingRunType;
use App\Enums\RegistrationStatus;
use App\Models\JudgeEvaluation;
use App\Models\RankingRun;
use App\Models\Registration;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesRankingFixtures;
use Tests\TestCase;

class DrawResolutionTest extends TestCase
{
    use CreatesRankingFixtures, LazilyRefreshDatabase;

    private function drawPayload(array $f, RankingRun $run): array
    {
        $ids = [$f['first']->id, $f['second']->id];
        sort($ids, SORT_NUMERIC);

        return ['group_key' => hash('sha256', json_encode($ids)), 'ordered_registration_ids' => [$f['second']->id, $f['first']->id],
            'note' => 'Dadu nyata: peserta kedua 6, peserta pertama 3.', 'confirmation' => 1];
    }

    public function test_admin_records_real_life_draw_order_without_rewriting_topsis_then_finalizes(): void
    {
        $f = $this->rankingFixture(tied: true);
        $run = app(RecalculateProvisionalRankingAction::class)->execute($f['class']);
        $original = $run->results()->get()->toArray();
        $finalizeUrl = route('admin.events.classes.ranking.finalize', [$f['event'], $f['class']]);
        $this->actingAs($f['admin'])->post($finalizeUrl, ['confirmation' => 'FINALISASI'])->assertSessionHasErrors('ranking');

        $this->post(route('admin.events.classes.ranking.draw', [$f['event'], $f['class'], $run]), $this->drawPayload($f, $run))->assertSessionHasNoErrors();

        $resolution = $run->tieResolutions()->sole();
        $this->assertSame([$f['second']->id, $f['first']->id], $resolution->ordered_registration_ids);
        $this->assertSame($f['admin']->id, $resolution->created_by);
        $this->assertSame($original, $run->results()->get()->toArray());
        $this->assertDatabaseHas('activity_logs', ['action' => ActivityAction::DrawResultRecorded->value, 'subject_id' => $resolution->id]);
        $this->post($finalizeUrl, ['confirmation' => 'FINALISASI'])->assertSessionHasNoErrors();
        $final = RankingRun::query()->where('type', RankingRunType::Final)->sole();
        $this->assertSame([$f['second']->id, $f['first']->id], $final->results()->orderBy('rank')->pluck('registration_id')->all());
        $this->assertSame([1, 2], $final->results()->orderBy('rank')->pluck('rank')->all());
        $this->assertSame(['0.5000000000'], $final->results()->distinct()->pluck('preference_value')->all());
        $this->assertCount(1, $final->calculation_snapshot['draw_resolutions']);
        $this->assertSame(2, $final->results()->where('is_tied', true)->count());
    }

    public function test_duplicate_participant_outsider_missing_confirmation_and_non_tie_cannot_override_rank(): void
    {
        $f = $this->rankingFixture(tied: true);
        $run = app(RecalculateProvisionalRankingAction::class)->execute($f['class']);
        $url = route('admin.events.classes.ranking.draw', [$f['event'], $f['class'], $run]);
        $payload = $this->drawPayload($f, $run);
        $this->actingAs($f['admin'])->post($url, [...$payload, 'ordered_registration_ids' => [$f['first']->id, $f['first']->id]])->assertSessionHasErrors('ordered_registration_ids.0');
        $this->post($url, [...$payload, 'ordered_registration_ids' => [$f['first']->id, 999999]])->assertSessionHasErrors('draw');
        $this->post($url, [...$payload, 'confirmation' => 0])->assertSessionHasErrors('confirmation');
        $this->post($url, [...$payload, 'note' => ''])->assertSessionHasErrors('note');
        $this->post($url, [...$payload, 'group_key' => str_repeat('a', 64)])->assertSessionHasErrors('draw');
        $this->assertDatabaseCount('ranking_tie_resolutions', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_recorded_draw_is_immutable_and_stale_source_cannot_be_used(): void
    {
        $f = $this->rankingFixture(tied: true);
        $run = app(RecalculateProvisionalRankingAction::class)->execute($f['class']);
        $url = route('admin.events.classes.ranking.draw', [$f['event'], $f['class'], $run]);
        $payload = $this->drawPayload($f, $run);
        $this->actingAs($f['admin'])->post($url, $payload)->assertSessionHasNoErrors();
        $this->post($url, [...$payload, 'ordered_registration_ids' => [$f['first']->id, $f['second']->id]])->assertSessionHasErrors('draw');
        $this->assertDatabaseCount('ranking_tie_resolutions', 1);
        $f['first']->bird()->first()->update(['name' => 'Nama berubah sebelum final']);
        $this->post(route('admin.events.classes.ranking.finalize', [$f['event'], $f['class']]), ['confirmation' => 'FINALISASI'])->assertSessionHasErrors('ranking');
        $this->post($url, $payload)->assertSessionHasErrors('draw');
        $fresh = app(RecalculateProvisionalRankingAction::class)->execute($f['class']);
        $this->assertNotSame($run->id, $fresh->id);
        $this->assertSame(0, $fresh->tieResolutions()->count());
    }

    public function test_dq_invalidates_old_draw_and_finalization_recalculates_remaining_participant(): void
    {
        $f = $this->rankingFixture(tied: true);
        $run = app(RecalculateProvisionalRankingAction::class)->execute($f['class']);
        $this->actingAs($f['admin'])->post(route('admin.events.classes.ranking.draw', [$f['event'], $f['class'], $run]), $this->drawPayload($f, $run))->assertSessionHasNoErrors();
        $f['second']->update(['status' => RegistrationStatus::Disqualified, 'disqualified_at' => now()]);

        $this->post(route('admin.events.classes.ranking.finalize', [$f['event'], $f['class']]), ['confirmation' => 'FINALISASI'])->assertSessionHasNoErrors();

        $final = RankingRun::query()->where('type', RankingRunType::Final)->sole();
        $this->assertSame($f['first']->id, $final->results()->sole()->registration_id);
        $this->assertSame([], $final->calculation_snapshot['draw_resolutions']);
    }

    public function test_no_draw_resolution_can_be_recorded_for_unequal_coefficients(): void
    {
        $f = $this->rankingFixture();
        $run = app(RecalculateProvisionalRankingAction::class)->execute($f['class']);
        $this->actingAs($f['admin'])->post(route('admin.events.classes.ranking.draw', [$f['event'], $f['class'], $run]), $this->drawPayload($f, $run))->assertSessionHasErrors('draw');
        $this->assertDatabaseCount('ranking_tie_resolutions', 0);
    }

    public function test_draw_in_middle_of_ranking_cannot_move_higher_or_lower_non_tied_participants(): void
    {
        $f = $this->rankingFixture(tied: true);
        $extraIds = [];
        foreach ([9, 7] as $score) {
            $registration = Registration::factory()->for($f['class'], 'competitionClass')->create(['status' => RegistrationStatus::Verified]);
            $extraIds[] = $registration->id;
            foreach ($f['judges'] as $judge) {
                $evaluation = JudgeEvaluation::factory()->for($registration)->for($judge, 'judge')->submitted()->create();
                $evaluation->scores()->createMany($f['criteria']->map(fn ($criterion) => ['criterion_id' => $criterion->id, 'score' => $score])->all());
            }
        }
        $run = app(RecalculateProvisionalRankingAction::class)->execute($f['class']);

        $this->actingAs($f['admin'])->post(route('admin.events.classes.ranking.draw', [$f['event'], $f['class'], $run]), $this->drawPayload($f, $run))->assertSessionHasNoErrors();
        $this->post(route('admin.events.classes.ranking.finalize', [$f['event'], $f['class']]), ['confirmation' => 'FINALISASI'])->assertSessionHasNoErrors();

        $final = RankingRun::query()->where('type', RankingRunType::Final)->sole();
        $this->assertSame([$extraIds[0], $f['second']->id, $f['first']->id, $extraIds[1]], $final->results()->orderBy('rank')->pluck('registration_id')->all());
        $this->assertSame([1, 2, 3, 4], $final->results()->orderBy('rank')->pluck('rank')->all());
    }
}
