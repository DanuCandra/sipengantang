<?php

namespace Tests\Feature\Participant;

use App\Enums\ActivityAction;
use App\Enums\CompetitionClassStatus;
use App\Enums\RegistrationStatus;
use App\Models\ActivityLog;
use App\Models\Bird;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class GantanganNumberTest extends TestCase
{
    use LazilyRefreshDatabase;

    private function fixture(): array
    {
        $participant = User::factory()->participant()->create();
        $event = Event::factory()->publishedUpcoming()->create();
        $class = CompetitionClass::factory()->for($event)->create(['bird_type' => 'Murai Batu']);
        $bird = Bird::factory()->for($participant, 'owner')->create(['bird_type' => 'Murai Batu']);

        return compact('participant', 'event', 'class', 'bird');
    }

    public function test_participant_reserves_number_immediately_on_registration_without_admin_or_payment_approval(): void
    {
        $f = $this->fixture();
        $this->actingAs($f['participant'])->get(route('participant.registrations.create', ['event' => $f['event'], 'class' => $f['class']->id]))->assertSee('Nomor gantangan pilihan')->assertSee('tanpa verifikasi Admin');
        $this->post(route('participant.registrations.store'), ['competition_class_id' => $f['class']->id, 'bird_id' => $f['bird']->id, 'gantangan_number' => '0017', 'participant_id' => 9999])->assertSessionHasNoErrors();

        $registration = Registration::query()->sole();
        $this->assertSame('17', $registration->gantangan_number);
        $this->assertSame($f['participant']->id, $registration->participant_id);
        $this->assertSame(RegistrationStatus::PendingPayment, $registration->status);
        $this->assertDatabaseCount('payment_submissions', 0);
        $this->assertSame('17', ActivityLog::query()->where('action', ActivityAction::RegistrationCreated)->sole()->metadata['gantangan_number']);
        $this->get(route('participant.registrations.show', $registration))->assertSee('Nomor sudah tersimpan dan terkunci.')->assertDontSee('Simpan nomor gantangan');
    }

    public function test_used_number_returns_clear_field_notification_but_same_number_in_other_class_is_allowed(): void
    {
        $f = $this->fixture();
        Registration::factory()->for($f['class'])->create(['gantangan_number' => '17', 'status' => RegistrationStatus::PaymentRejected]);
        $payload = ['competition_class_id' => $f['class']->id, 'bird_id' => $f['bird']->id, 'gantangan_number' => '017'];
        $this->actingAs($f['participant'])->post(route('participant.registrations.store'), $payload)->assertSessionHasErrors(['gantangan_number' => 'Nomor gantangan sudah digunakan pada kelas ini. Silakan pilih nomor lain.']);
        $this->assertDatabaseCount('registrations', 1);
        $this->assertDatabaseCount('activity_logs', 0);

        $other = CompetitionClass::factory()->for($f['event'])->create(['bird_type' => 'Murai Batu']);
        $this->post(route('participant.registrations.store'), [...$payload, 'competition_class_id' => $other->id])->assertSessionHasNoErrors();
        $this->assertDatabaseCount('registrations', 2);
    }

    #[DataProvider('invalidNumbers')]
    public function test_missing_or_invalid_number_is_rejected_without_registration(mixed $number): void
    {
        $f = $this->fixture();
        $this->actingAs($f['participant'])->post(route('participant.registrations.store'), ['competition_class_id' => $f['class']->id, 'bird_id' => $f['bird']->id, 'gantangan_number' => $number])->assertSessionHasErrors('gantangan_number');
        $this->assertDatabaseCount('registrations', 0);
    }

    public static function invalidNumbers(): array
    {
        return [[null], [''], ['0'], ['000'], ['-1'], ['1.5'], ['1e2'], ['A17'], [str_repeat('9', 51)], [['17']]];
    }

    public function test_existing_registration_can_choose_once_and_status_and_audit_remain_consistent(): void
    {
        $f = $this->fixture();
        $registration = Registration::factory()->for($f['class'])->for($f['participant'], 'participant')->create(['status' => RegistrationStatus::Verified, 'verified_at' => now()]);
        $url = route('participant.registrations.gantangan-number', $registration);
        $this->actingAs($f['participant'])->get(route('participant.registrations.show', $registration))->assertSee('Pilih nomor gantangan Anda');
        $this->post($url, ['gantangan_number' => '21'])->assertSessionHasNoErrors();
        $this->assertSame('21', $registration->fresh()->gantangan_number);
        $this->assertSame(RegistrationStatus::Verified, $registration->fresh()->status);
        $this->assertSame(1, ActivityLog::query()->where('action', ActivityAction::GantanganNumberChosen)->count());
        $this->post($url, ['gantangan_number' => '22'])->assertSessionHasErrors('gantangan_number');
        $this->assertSame('21', $registration->fresh()->gantangan_number);
        $this->assertSame(1, ActivityLog::query()->where('action', ActivityAction::GantanganNumberChosen)->count());
    }

    public function test_legacy_zero_padded_number_is_treated_as_occupied_and_error_preserves_empty_number(): void
    {
        $f = $this->fixture();
        Registration::factory()->for($f['class'])->create(['gantangan_number' => '0009']);
        $registration = Registration::factory()->for($f['class'])->for($f['participant'], 'participant')->create();
        $this->actingAs($f['participant'])->post(route('participant.registrations.gantangan-number', $registration), ['gantangan_number' => '9'])->assertSessionHasErrors('gantangan_number');
        $this->assertNull($registration->fresh()->gantangan_number);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_other_participant_admin_and_guest_cannot_choose_a_registrations_number(): void
    {
        $f = $this->fixture();
        $registration = Registration::factory()->for($f['class'])->for($f['participant'], 'participant')->create();
        $url = route('participant.registrations.gantangan-number', $registration);
        $this->post($url, ['gantangan_number' => '7'])->assertRedirect(route('login'));
        foreach ([User::factory()->participant()->create(), User::factory()->admin()->create(), $f['event']->owner] as $user) {
            $this->actingAs($user)->post($url, ['gantangan_number' => '7'])->assertForbidden();
        }
        $this->assertNull($registration->fresh()->gantangan_number);
    }

    public function test_number_cannot_be_chosen_after_judging_or_for_disqualified_or_cancelled_registration(): void
    {
        $f = $this->fixture();
        $registration = Registration::factory()->for($f['class'])->for($f['participant'], 'participant')->create();
        $url = route('participant.registrations.gantangan-number', $registration);
        foreach ([CompetitionClassStatus::Ongoing, CompetitionClassStatus::Finalized] as $status) {
            $f['class']->update(['status' => $status]);
            $this->actingAs($f['participant'])->post($url, ['gantangan_number' => '8'])->assertSessionHasErrors('gantangan_number');
        }
        $f['class']->update(['status' => CompetitionClassStatus::Upcoming]);
        foreach ([RegistrationStatus::Disqualified, RegistrationStatus::Cancelled] as $status) {
            $registration->update(['status' => $status]);
            $this->post($url, ['gantangan_number' => '8'])->assertSessionHasErrors('gantangan_number');
        }
        $this->assertNull($registration->fresh()->gantangan_number);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_database_rejects_duplicate_number_inside_a_class_even_outside_the_action(): void
    {
        $f = $this->fixture();
        Registration::factory()->for($f['class'])->create(['gantangan_number' => '33']);
        $this->expectException(UniqueConstraintViolationException::class);
        Registration::factory()->for($f['class'])->create(['gantangan_number' => '33']);
    }

    public function test_migration_detects_conflicting_legacy_numbers_without_rewriting_history(): void
    {
        $f = $this->fixture();
        Registration::factory()->for($f['class'])->create(['gantangan_number' => '17']);
        Registration::factory()->for($f['class'])->create(['gantangan_number' => '017']);
        $migration = require database_path('migrations/2026_10_29_205837_add_unique_gantangan_number_per_class_to_registrations_table.php');
        try {
            $migration->up();
            $this->fail('Conflicting legacy numbers must require review.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('nomor gantangan duplikat', $exception->getMessage());
        }
        $this->assertSame(['17', '017'], $f['class']->registrations()->orderBy('id')->pluck('gantangan_number')->all());
    }
}
