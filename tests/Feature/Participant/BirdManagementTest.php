<?php

namespace Tests\Feature\Participant;

use App\Enums\ActivityAction;
use App\Models\Bird;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BirdManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_participant_can_view_bird_list_and_own_bird_detail(): void
    {
        $participant = User::factory()->participant()->create();
        $bird = Bird::factory()->for($participant, 'owner')->create(['name' => 'Panglima']);

        $this->actingAs($participant)->get(route('participant.birds.index'))
            ->assertOk()
            ->assertSee('Panglima');

        $this->actingAs($participant)->get(route('participant.birds.show', $bird))
            ->assertOk()
            ->assertSee('Panglima');
    }

    public function test_participant_can_create_bird_with_safe_photo(): void
    {
        Storage::fake('public');
        $participant = User::factory()->participant()->create();

        $response = $this->actingAs($participant)->post(route('participant.birds.store'), [
            'name' => 'Panglima',
            'bird_type' => 'Murai Batu',
            'photo' => UploadedFile::fake()->image('panglima.jpg', 800, 800),
            'description' => 'Irama stabil.',
        ]);

        $bird = Bird::query()->sole();
        $response->assertRedirect(route('participant.birds.show', $bird));
        $this->assertSame($participant->id, $bird->owner_id);
        Storage::disk('public')->assertExists($bird->photo_path);
        $this->assertDatabaseHas('activity_logs', ['action' => ActivityAction::BirdCreated->value, 'subject_id' => $bird->id]);
    }

    public function test_participant_cannot_open_or_update_another_participants_bird(): void
    {
        $participant = User::factory()->participant()->create();
        $otherBird = Bird::factory()->create();

        $this->actingAs($participant)->get(route('participant.birds.show', $otherBird))->assertForbidden();
        $this->actingAs($participant)->put(route('participant.birds.update', $otherBird), [
            'name' => 'Diubah',
            'bird_type' => 'Kacer',
        ])->assertForbidden();

        $this->assertNotSame('Diubah', $otherBird->refresh()->name);
    }

    public function test_bird_photo_rejects_non_image_file(): void
    {
        Storage::fake('public');
        $participant = User::factory()->participant()->create();

        $response = $this->actingAs($participant)->post(route('participant.birds.store'), [
            'name' => 'Panglima',
            'bird_type' => 'Murai Batu',
            'photo' => UploadedFile::fake()->create('payload.php', 20, 'text/x-php'),
        ]);

        $response->assertSessionHasErrors('photo');
        $this->assertDatabaseCount('birds', 0);
    }
}
