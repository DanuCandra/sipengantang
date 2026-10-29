<?php

namespace Tests\Feature\Navigation;

use App\Enums\CompetitionClassStatus;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\EventAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\Concerns\CreatesRankingFixtures;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use CreatesRankingFixtures, LazilyRefreshDatabase;

    public function test_admin_sidebar_shows_operational_menus_and_empty_workspace_without_assignment(): void
    {
        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertSee('Event aktif')->assertSee('Kelas Lomba')->assertSee('Pembayaran')->assertSee('Monitor Penjurian')->assertSee('Hasil &amp; Piagam', false)->assertSee('Pelanggaran');
        $this->get(route('admin.workspace', ['section' => 'classes']))->assertOk()->assertSee('Belum ada event ditugaskan');
    }

    public function test_admin_class_workspaces_and_event_switcher_only_expose_assigned_event_data(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create(['name' => 'Event Pilihan']);
        EventAdmin::factory()->for($event)->for($admin, 'user')->create();
        $class = CompetitionClass::factory()->for($event)->create(['name' => 'Kelas Saya']);
        $other = Event::factory()->create(['name' => 'Event Rahasia']);
        CompetitionClass::factory()->for($other)->create(['name' => 'Kelas Rahasia']);

        foreach (['classes', 'judges', 'judging', 'results', 'prizes'] as $section) {
            $this->actingAs($admin)->get(route('admin.workspace', ['section' => $section, 'event_id' => $event->id]))
                ->assertOk()->assertSee($class->name)->assertSee($event->name)->assertDontSee($other->name)->assertDontSee('Kelas Rahasia');
        }
        $this->get(route('admin.workspace', ['section' => 'payments', 'event_id' => $event->id]))->assertRedirect(route('admin.events.payments.index', $event));
        $this->get(route('admin.workspace', ['section' => 'classes', 'event_id' => $other->id]))->assertForbidden();
        $this->get('/admin/workspace/arbitrary')->assertNotFound();
        $this->actingAs(User::factory()->participant()->create())->get(route('admin.workspace', ['section' => 'classes']))->assertForbidden();
    }

    public function test_switching_event_changes_all_operational_targets_and_revoked_session_falls_back_safely(): void
    {
        $admin = User::factory()->admin()->create();
        $events = Event::factory()->count(2)->create();
        foreach ($events as $event) {
            EventAdmin::factory()->for($event)->for($admin, 'user')->create();
        }
        $this->actingAs($admin)->get(route('admin.workspace', ['section' => 'classes', 'event_id' => $events[1]->id]))
            ->assertSessionHas('dashboard.admin.event_id', $events[1]->id)->assertSee(route('admin.workspace', ['section' => 'results', 'event_id' => $events[1]->id]));
        $events[1]->adminAssignments()->delete();
        $this->withSession(['dashboard.admin.event_id' => $events[1]->id])->get(route('admin.workspace', ['section' => 'classes']))->assertSee($events[0]->name)->assertDontSee($events[1]->name);
    }

    public function test_owner_workspace_lists_only_owned_events_and_links_to_assignment_dropdown(): void
    {
        $owner = User::factory()->owner()->create();
        $own = Event::factory()->for($owner, 'owner')->create(['name' => 'Milik Owner']);
        $other = Event::factory()->create(['name' => 'Bukan Milik Owner']);
        $this->actingAs($owner)->get(route('owner.workspace', ['section' => 'admins']))->assertOk()->assertSee($own->name)->assertDontSee($other->name)->assertSee(route('owner.events.show', $own).'#tim-admin');
        $this->get(route('owner.workspace', ['section' => 'monitoring']))->assertSee(route('owner.events.show', $own).'#pantau-penjurian');
        $this->actingAs(User::factory()->admin()->create())->get(route('owner.workspace', ['section' => 'admins']))->assertForbidden();
    }

    public function test_judge_sidebar_filters_scoring_and_history_to_own_active_assignments(): void
    {
        $f = $this->rankingFixture();
        $this->actingAs($f['judges'][0])->get(route('judge.classes.index', ['view' => 'history']))->assertSee('Riwayat Nilai Saya')->assertSee($f['class']->name);
        $this->get(route('judge.classes.index', ['view' => 'scoring']))->assertSee($f['class']->name)->assertSee('Penilaian Aktif');
        $f['class']->update(['status' => CompetitionClassStatus::Finalized]);
        $this->get(route('judge.classes.index', ['view' => 'scoring']))->assertDontSee($f['class']->name);
        $other = User::factory()->judge()->create();
        $this->actingAs($other)->get(route('judge.classes.index', ['view' => 'history']))->assertDontSee($f['class']->name);
    }

    public function test_participant_sidebar_retains_all_participant_resources(): void
    {
        $this->actingAs(User::factory()->participant()->create())->get(route('participant.dashboard'))
            ->assertSee(route('participant.birds.index'))->assertSee(route('participant.registrations.index'))
            ->assertSee(route('participant.certificates.index'))->assertSee(route('participant.profile.edit'));
    }
}
