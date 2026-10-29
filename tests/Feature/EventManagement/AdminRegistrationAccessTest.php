<?php

namespace Tests\Feature\EventManagement;

use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\EventAdmin;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminRegistrationAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_assigned_admin_can_view_event_registrations(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        EventAdmin::factory()->for($event)->for($admin, 'user')->create();
        $competitionClass = CompetitionClass::factory()->for($event)->create();
        $registration = Registration::factory()->for($competitionClass)->create();

        $this->actingAs($admin)->get(route('admin.events.registrations.index', $event))
            ->assertSee($registration->registration_number);
        $this->actingAs($admin)->get(route('admin.events.registrations.show', [$event, $registration]))
            ->assertSee($registration->participant()->value('name'));
    }

    public function test_unassigned_admin_cannot_view_event_registrations(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)->get(route('admin.events.registrations.index', $event))->assertForbidden();
    }

    public function test_registration_from_another_event_returns_not_found(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $otherEvent = Event::factory()->create();
        EventAdmin::factory()->for($event)->for($admin, 'user')->create();
        $otherClass = CompetitionClass::factory()->for($otherEvent)->create();
        $registration = Registration::factory()->for($otherClass)->create();

        $this->actingAs($admin)->get(route('admin.events.registrations.show', [$event, $registration]))->assertNotFound();
    }
}
