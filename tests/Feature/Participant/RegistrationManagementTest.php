<?php

namespace Tests\Feature\Participant;

use App\Enums\ActivityAction;
use App\Enums\RegistrationStatus;
use App\Models\Bird;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class RegistrationManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_participant_can_open_registration_form_and_own_registration_detail(): void
    {
        [$participant, $event, $competitionClass, $bird] = $this->registrationFixture();

        $this->actingAs($participant)->get(route('participant.registrations.create', [
            'event' => $event,
            'class' => $competitionClass->id,
        ]))
            ->assertOk()
            ->assertSee($competitionClass->name)
            ->assertSee($bird->name);

        $registration = Registration::factory()
            ->for($competitionClass)
            ->for($participant, 'participant')
            ->for($bird)
            ->create();

        $this->actingAs($participant)->get(route('participant.registrations.show', $registration))
            ->assertOk()
            ->assertSee($registration->registration_number);
    }

    public function test_participant_can_register_owned_compatible_bird_with_fee_snapshot(): void
    {
        [$participant, $event, $competitionClass, $bird] = $this->registrationFixture();

        $response = $this->actingAs($participant)->post(route('participant.registrations.store'), [
            'competition_class_id' => $competitionClass->id,
            'bird_id' => $bird->id,
            'gantangan_number' => '17',
        ]);

        $registration = Registration::query()->sole();
        $response->assertRedirect(route('participant.registrations.show', $registration));
        $this->assertSame($participant->id, $registration->participant_id);
        $this->assertSame(175000, $registration->registration_fee_snapshot);
        $this->assertSame(RegistrationStatus::PendingPayment, $registration->status);
        $this->assertMatchesRegularExpression('/^REG-\d{4}-\d{6,}$/', $registration->registration_number);
        $this->assertDatabaseHas('activity_logs', ['action' => ActivityAction::RegistrationCreated->value, 'subject_id' => $registration->id]);

        $competitionClass->update(['registration_fee' => 250000]);
        $this->assertSame(175000, $registration->refresh()->registration_fee_snapshot);
    }

    public function test_registration_rejects_bird_owned_by_another_participant(): void
    {
        [$participant, , $competitionClass] = $this->registrationFixture();
        $otherBird = Bird::factory()->create(['bird_type' => 'Murai Batu']);

        $response = $this->actingAs($participant)->post(route('participant.registrations.store'), [
            'competition_class_id' => $competitionClass->id,
            'bird_id' => $otherBird->id,
            'gantangan_number' => '17',
        ]);

        $response->assertSessionHasErrors('bird_id');
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_registration_rejects_incompatible_bird_type(): void
    {
        [$participant, , $competitionClass, $bird] = $this->registrationFixture();
        $bird->update(['bird_type' => 'Kacer']);

        $response = $this->actingAs($participant)->post(route('participant.registrations.store'), [
            'competition_class_id' => $competitionClass->id,
            'bird_id' => $bird->id,
            'gantangan_number' => '17',
        ]);

        $response->assertSessionHasErrors('bird_id');
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_registration_rejects_full_class(): void
    {
        [$participant, , $competitionClass, $bird] = $this->registrationFixture(quota: 1);
        Registration::factory()->for($competitionClass)->create();

        $response = $this->actingAs($participant)->post(route('participant.registrations.store'), [
            'competition_class_id' => $competitionClass->id,
            'bird_id' => $bird->id,
            'gantangan_number' => '17',
        ]);

        $response->assertSessionHasErrors('competition_class_id');
        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_registration_rejects_closed_event(): void
    {
        [$participant, $event, $competitionClass, $bird] = $this->registrationFixture();
        $event->update(['registration_close_at' => now()->subMinute()]);

        $response = $this->actingAs($participant)->post(route('participant.registrations.store'), [
            'competition_class_id' => $competitionClass->id,
            'bird_id' => $bird->id,
            'gantangan_number' => '17',
        ]);

        $response->assertSessionHasErrors('competition_class_id');
        $this->assertDatabaseCount('registrations', 0);
    }

    public function test_registration_rejects_same_bird_twice_in_one_class_but_allows_another_class(): void
    {
        [$participant, $event, $competitionClass, $bird] = $this->registrationFixture();
        $otherClass = CompetitionClass::factory()->for($event)->create(['bird_type' => 'Murai Batu']);

        $this->actingAs($participant)->post(route('participant.registrations.store'), [
            'competition_class_id' => $competitionClass->id,
            'bird_id' => $bird->id,
            'gantangan_number' => '17',
        ])->assertRedirect();
        $this->actingAs($participant)->post(route('participant.registrations.store'), [
            'competition_class_id' => $competitionClass->id,
            'bird_id' => $bird->id,
            'gantangan_number' => '17',
        ])->assertSessionHasErrors('bird_id');
        $this->actingAs($participant)->post(route('participant.registrations.store'), [
            'competition_class_id' => $otherClass->id,
            'bird_id' => $bird->id,
            'gantangan_number' => '17',
        ])->assertRedirect();

        $this->assertDatabaseCount('registrations', 2);
    }

    public function test_participant_cannot_view_another_participants_registration(): void
    {
        $participant = User::factory()->participant()->create();
        $registration = Registration::factory()->create();

        $this->actingAs($participant)->get(route('participant.registrations.show', $registration))->assertForbidden();
    }

    /** @return array{User, Event, CompetitionClass, Bird} */
    private function registrationFixture(?int $quota = 20): array
    {
        $participant = User::factory()->participant()->create();
        $event = Event::factory()->publishedUpcoming()->create();
        $competitionClass = CompetitionClass::factory()->for($event)->create([
            'bird_type' => 'Murai Batu',
            'registration_fee' => 175000,
            'quota' => $quota,
        ]);
        $bird = Bird::factory()->for($participant, 'owner')->create(['bird_type' => 'Murai Batu']);

        return [$participant, $event, $competitionClass, $bird];
    }
}
