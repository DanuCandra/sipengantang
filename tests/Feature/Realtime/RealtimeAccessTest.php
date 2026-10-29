<?php

namespace Tests\Feature\Realtime;

use App\Domain\Ranking\Actions\FinalizeClassResultAction;
use App\Domain\Ranking\Actions\RecalculateProvisionalRankingAction;
use App\Enums\RegistrationStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesRankingFixtures;
use Tests\TestCase;

class RealtimeAccessTest extends TestCase
{
    use CreatesRankingFixtures, LazilyRefreshDatabase;

    private function configureBroadcastAuth(): void
    {
        config(['broadcasting.default' => 'reverb', 'broadcasting.connections.reverb.key' => 'test-public-key', 'broadcasting.connections.reverb.secret' => 'test-secret', 'broadcasting.connections.reverb.app_id' => 'test-app']);
        // Tests boot with the null broadcaster; register the real callbacks on this
        // test's Reverb driver just as normal application boot registers them.
        require base_path('routes/channels.php');
    }

    public function test_private_admin_channel_allows_assigned_admin_owner_but_not_unrelated_roles(): void
    {
        $f = $this->rankingFixture();
        $this->configureBroadcastAuth();
        $payload = ['socket_id' => '123.456', 'channel_name' => 'private-event.'.$f['event']->id.'.admin'];
        $this->postJson('/broadcasting/auth', $payload)->assertUnauthorized();
        foreach ([$f['admin'], $f['event']->owner] as $user) {
            $this->actingAs($user)->postJson('/broadcasting/auth', $payload)->assertOk()->assertJsonStructure(['auth']);
        }
        foreach ([User::factory()->admin()->create(), $f['judges'][0], $f['first']->participant] as $user) {
            $this->actingAs($user)->postJson('/broadcasting/auth', $payload)->assertForbidden();
        }
    }

    public function test_judge_and_participant_channels_require_own_identity_and_current_assignment(): void
    {
        $f = $this->rankingFixture();
        $this->configureBroadcastAuth();
        $judge = $f['judges'][0];
        $prefix = 'private-class.'.$f['class']->id.'.judge.';
        $this->actingAs($judge)->postJson('/broadcasting/auth', ['socket_id' => '123.456', 'channel_name' => $prefix.$judge->id])->assertOk();
        $this->postJson('/broadcasting/auth', ['socket_id' => '123.456', 'channel_name' => $prefix.$f['judges'][1]->id])->assertForbidden();
        $this->actingAs(User::factory()->judge()->create())->postJson('/broadcasting/auth', ['socket_id' => '123.456', 'channel_name' => $prefix.$judge->id])->assertForbidden();
        $participant = $f['first']->participant;
        $this->actingAs($participant)->postJson('/broadcasting/auth', ['socket_id' => '123.456', 'channel_name' => 'private-user.'.$participant->id])->assertOk();
        $this->postJson('/broadcasting/auth', ['socket_id' => '123.456', 'channel_name' => 'private-user.'.$judge->id])->assertForbidden();
        $f['class']->judgeAssignments()->where('judge_id', $judge->id)->update(['replaced_at' => now()]);
        $this->actingAs($judge)->postJson('/broadcasting/auth', ['socket_id' => '123.456', 'channel_name' => $prefix.$judge->id])->assertForbidden();
    }

    public function test_public_snapshot_has_only_progress_and_final_availability_not_private_or_provisional_data(): void
    {
        $f = $this->rankingFixture();
        app(RecalculateProvisionalRankingAction::class)->execute($f['class']);
        $response = $this->getJson(route('events.classes.live', [$f['event'], $f['class']]));
        $response->assertOk()->assertJsonPath('submitted', 6)->assertJsonPath('expected', 6)->assertJsonPath('percent', 100)->assertJsonPath('result_url', null);
        $this->assertSame(['class_id', 'status', 'eligible', 'submitted', 'expected', 'percent', 'disqualified', 'result_url'], array_keys($response->json()));
        $response->assertDontSee('ranking_run_id')->assertDontSee('preference_value')->assertDontSee('judge_id')->assertDontSee($f['first']->participant->email);
        app(FinalizeClassResultAction::class)->execute($f['admin'], $f['class']);
        $this->getJson(route('events.classes.live', [$f['event'], $f['class']]))->assertJsonPath('status', 'FINALIZED')->assertJsonPath('result_url', route('events.classes.results', [$f['event'], $f['class']]));
    }

    public function test_cross_event_unpublished_and_unassigned_snapshot_requests_are_rejected(): void
    {
        $f = $this->rankingFixture();
        $other = $this->rankingFixture();
        $this->getJson(route('events.classes.live', [$f['event'], $other['class']]))->assertNotFound();
        $f['event']->update(['published_at' => null]);
        $this->getJson(route('events.classes.live', [$f['event'], $f['class']]))->assertNotFound();
        $this->actingAs(User::factory()->admin()->create())->getJson(route('admin.events.classes.live', [$f['event'], $f['class']]))->assertForbidden();
        $this->actingAs(User::factory()->judge()->create())->getJson(route('judge.classes.live', $f['class']))->assertForbidden();
        $this->actingAs($f['admin'])->getJson(route('admin.events.classes.live', [$f['event'], $other['class']]))->assertNotFound();
        $this->actingAs($f['event']->owner)->getJson(route('owner.events.classes.live', [$f['event'], $f['class']]))->assertOk();
    }

    public function test_judge_snapshot_contains_only_own_evaluation_state_without_any_judge_scores(): void
    {
        $f = $this->rankingFixture();
        $response = $this->actingAs($f['judges'][0])->getJson(route('judge.classes.live', [$f['class'], 'registrations' => [$f['first']->id]]));
        $response->assertOk()->assertJsonPath('registrations.0.evaluation_status', 'SUBMITTED');
        $this->assertSame(['id', 'status', 'evaluation_status'], array_keys($response->json('registrations.0')));
        $response->assertDontSee('scores')->assertDontSee('judge_id')->assertDontSee('note')->assertDontSee('C1');
        $other = $this->rankingFixture();
        $this->getJson(route('judge.classes.live', [$f['class'], 'registrations' => [$other['first']->id]]))->assertJsonCount(0, 'registrations');
    }

    public function test_admin_snapshot_resets_each_judge_counter_after_all_entries_are_disqualified(): void
    {
        $f = $this->rankingFixture();
        $f['class']->registrations()->update(['status' => RegistrationStatus::Disqualified]);
        $response = $this->actingAs($f['admin'])->getJson(route('admin.events.classes.live', [$f['event'], $f['class']]));
        $response->assertOk()->assertJsonPath('submitted', 0);
        foreach ($f['judges'] as $judge) {
            $response->assertJsonPath('by_judge.'.$judge->id.'.SUBMITTED', 0)->assertJsonPath('by_judge.'.$judge->id.'.DRAFT', 0);
        }
    }
}
