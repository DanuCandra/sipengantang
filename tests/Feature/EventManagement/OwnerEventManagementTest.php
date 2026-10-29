<?php

namespace Tests\Feature\EventManagement;

use App\Enums\ActivityAction;
use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class OwnerEventManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_owner_can_create_event_as_draft_with_activity_log(): void
    {
        $owner = User::factory()->owner()->create();

        $response = $this->actingAs($owner)->post(route('owner.events.store'), $this->eventPayload());

        $event = Event::query()->sole();
        $response->assertRedirect(route('owner.events.show', $event));
        $this->assertSame(EventStatus::Draft, $event->status);
        $this->assertSame($owner->id, $event->owner_id);
        $this->assertDatabaseHas('activity_logs', ['action' => ActivityAction::EventCreated->value, 'subject_id' => $event->id]);
    }

    public function test_owner_can_upload_safe_event_banner(): void
    {
        Storage::fake('public');
        $owner = User::factory()->owner()->create();
        $payload = [...$this->eventPayload(), 'banner' => UploadedFile::fake()->image('event.jpg', 1200, 600)];

        $this->actingAs($owner)->post(route('owner.events.store'), $payload)->assertRedirect();

        $event = Event::query()->sole();
        $this->assertNotNull($event->banner_path);
        Storage::disk('public')->assertExists($event->banner_path);
    }

    public function test_owner_cannot_open_another_owners_event(): void
    {
        $owner = User::factory()->owner()->create();
        $otherEvent = Event::factory()->create();

        $this->actingAs($owner)->get(route('owner.events.show', $otherEvent))->assertForbidden();
    }

    public function test_owner_event_detail_renders_management_sections(): void
    {
        $owner = User::factory()->owner()->create();
        $event = Event::factory()->for($owner, 'owner')->create();

        $this->actingAs($owner)->get(route('owner.events.show', $event))
            ->assertOk()
            ->assertSee($event->name)
            ->assertSee('Admin penanggung jawab');
    }

    public function test_owner_can_assign_only_an_admin_account(): void
    {
        $owner = User::factory()->owner()->create();
        $event = Event::factory()->for($owner, 'owner')->create();
        $admin = User::factory()->admin()->create();
        $participant = User::factory()->participant()->create();

        $this->actingAs($owner)->post(route('owner.events.admins.store', $event), ['email' => $admin->email])->assertRedirect();
        $this->assertDatabaseHas('event_admins', ['event_id' => $event->id, 'user_id' => $admin->id]);

        $this->actingAs($owner)->post(route('owner.events.admins.store', $event), ['email' => $participant->email])
            ->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('event_admins', ['event_id' => $event->id, 'user_id' => $participant->id]);
    }

    public function test_owner_can_publish_a_draft_event_explicitly(): void
    {
        $owner = User::factory()->owner()->create();
        $event = Event::factory()->for($owner, 'owner')->create();

        $this->actingAs($owner)->post(route('owner.events.publish', $event))->assertRedirect();

        $event->refresh();
        $this->assertSame(EventStatus::Upcoming, $event->status);
        $this->assertNotNull($event->published_at);
    }

    public function test_owner_can_create_and_assign_new_admin_account(): void
    {
        $owner = User::factory()->owner()->create();
        $event = Event::factory()->for($owner, 'owner')->create();

        $this->actingAs($owner)->post(route('owner.events.admins.accounts.store', $event), [
            'name' => 'Admin Lapangan',
            'email' => 'admin.lapangan@example.com',
            'phone' => '081222334455',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect();

        $admin = User::query()->where('email', 'admin.lapangan@example.com')->firstOrFail();
        $this->assertTrue($admin->load('roles')->hasRole(UserRole::Admin));
        $this->assertDatabaseHas('event_admins', ['event_id' => $event->id, 'user_id' => $admin->id]);
    }

    /** @return array<string, string> */
    private function eventPayload(): array
    {
        return [
            'name' => 'Piala Nusantara 2026',
            'description' => 'Kompetisi burung berkicau regional.',
            'location_name' => 'Arena Gantangan Utama',
            'address' => 'Jalan Merdeka No. 10, Bandung',
            'registration_open_at' => '2026-10-01 08:00:00',
            'registration_close_at' => '2026-10-20 18:00:00',
            'start_at' => '2026-10-25 08:00:00',
            'end_at' => '2026-10-25 17:00:00',
            'contact_person' => 'Panitia',
            'contact_phone' => '081234567890',
        ];
    }
}
