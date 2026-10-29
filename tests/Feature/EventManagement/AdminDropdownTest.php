<?php

namespace Tests\Feature\EventManagement;

use App\Models\Event;
use App\Models\EventAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminDropdownTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_dropdown_only_offers_active_unassigned_admin_accounts_and_keeps_email_assignment_contract(): void
    {
        $owner = User::factory()->owner()->create();
        $event = Event::factory()->for($owner, 'owner')->create();
        $active = User::factory()->admin()->create(['name' => 'Admin Pilihan', 'email' => 'pilihan@gmail.com']);
        User::factory()->inactive()->admin()->create(['email' => 'inactive@gmail.com']);
        User::factory()->participant()->create(['email' => 'participant@gmail.com']);
        $assigned = User::factory()->admin()->create();
        EventAdmin::factory()->for($event)->for($assigned, 'user')->create();

        $this->actingAs($owner)->get(route('owner.events.show', $event))->assertOk()->assertSee('Pilih akun Admin')->assertSee('pilihan@gmail.com')->assertDontSee('inactive@gmail.com')->assertDontSee('participant@gmail.com')
            ->assertViewHas('availableAdmins', fn ($admins) => $admins->modelKeys() === [$active->id]);
        $this->post(route('owner.events.admins.store', $event), ['email' => $active->email])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('event_admins', ['event_id' => $event->id, 'user_id' => $active->id]);
        $this->get(route('owner.events.show', $event))->assertViewHas('availableAdmins', fn ($admins) => $admins->isEmpty())->assertSee('Tidak ada akun Admin aktif yang belum ditugaskan');
    }

    public function test_tampered_dropdown_cannot_assign_inactive_or_non_admin_or_another_owners_event(): void
    {
        $owner = User::factory()->owner()->create();
        $event = Event::factory()->for($owner, 'owner')->create();
        foreach ([User::factory()->inactive()->admin()->create(), User::factory()->participant()->create()] as $invalid) {
            $this->actingAs($owner)->post(route('owner.events.admins.store', $event), ['email' => $invalid->email])->assertSessionHasErrors('email');
            $this->assertDatabaseMissing('event_admins', ['event_id' => $event->id, 'user_id' => $invalid->id]);
        }
        $other = Event::factory()->create();
        $admin = User::factory()->admin()->create();
        $this->post(route('owner.events.admins.store', $other), ['email' => $admin->email])->assertForbidden();
        $this->assertDatabaseMissing('event_admins', ['event_id' => $other->id, 'user_id' => $admin->id]);
    }
}
