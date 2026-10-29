<?php

namespace Tests\Feature\Participant;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class ProfileManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_participant_can_open_private_profile_form(): void
    {
        $participant = User::factory()->participant()->create();

        $this->actingAs($participant)->get(route('participant.profile.edit'))
            ->assertOk()
            ->assertSee($participant->email);
    }

    public function test_participant_can_update_private_profile(): void
    {
        $participant = User::factory()->participant()->create();

        $response = $this->actingAs($participant)->put(route('participant.profile.update'), [
            'name' => 'Budi Santoso',
            'phone' => '081234567890',
            'address' => 'Jalan Merdeka 10',
            'city' => 'Bandung',
            'province' => 'Jawa Barat',
            'postal_code' => '40111',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', ['id' => $participant->id, 'name' => 'Budi Santoso', 'phone' => '081234567890']);
        $this->assertDatabaseHas('participant_profiles', ['user_id' => $participant->id, 'city' => 'Bandung', 'province' => 'Jawa Barat']);
    }
}
