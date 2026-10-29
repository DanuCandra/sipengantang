<?php

namespace Tests\Feature\Payment;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Models\PaymentSubmission;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class RegistrationProofTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_verified_participant_can_preview_and_download_real_pdf(): void
    {
        $registration = Registration::factory()->create(['status' => RegistrationStatus::Verified, 'verified_at' => now()]);
        PaymentSubmission::factory()->for($registration)->create(['status' => PaymentStatus::Verified, 'reviewed_at' => now()]);
        $participant = $registration->participant()->firstOrFail();

        $response = $this->actingAs($participant)->get(route('participant.registrations.proof', $registration));

        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringStartsWith('inline;', $response->headers->get('Content-Disposition'));
        $this->assertTrue($response->headers->hasCacheControlDirective('no-store'));

        $download = $this->actingAs($participant)->get(route('participant.registrations.proof', [$registration, 'download' => 1]));
        $download->assertDownload('bukti-'.$registration->registration_number.'.pdf');
    }

    #[DataProvider('unverifiedStates')]
    public function test_unverified_registration_cannot_download_official_proof(RegistrationStatus $status): void
    {
        $registration = Registration::factory()->create(['status' => $status]);

        $this->actingAs($registration->participant()->firstOrFail())->get(route('participant.registrations.proof', $registration))->assertForbidden();
    }

    public static function unverifiedStates(): array
    {
        return [
            'pending' => [RegistrationStatus::PendingPayment],
            'waiting' => [RegistrationStatus::WaitingVerification],
            'rejected' => [RegistrationStatus::PaymentRejected],
            'cancelled' => [RegistrationStatus::Cancelled],
            'disqualified' => [RegistrationStatus::Disqualified],
        ];
    }

    public function test_other_participant_cannot_download_verified_registration_proof(): void
    {
        $registration = Registration::factory()->create(['status' => RegistrationStatus::Verified, 'verified_at' => now()]);
        PaymentSubmission::factory()->for($registration)->create(['status' => PaymentStatus::Verified]);

        $this->actingAs(User::factory()->participant()->create())->get(route('participant.registrations.proof', $registration))->assertForbidden();
    }

    public function test_verified_registration_without_verified_payment_cannot_issue_official_proof(): void
    {
        $registration = Registration::factory()->create(['status' => RegistrationStatus::Verified, 'verified_at' => now()]);

        $this->actingAs($registration->participant()->firstOrFail())->get(route('participant.registrations.proof', $registration))->assertForbidden();
    }

    public function test_proof_template_contains_required_competition_details_without_private_contact_or_payment_files(): void
    {
        $registration = Registration::factory()->create(['status' => RegistrationStatus::Verified, 'verified_at' => now(), 'gantangan_number' => 17]);
        $registration->load(['participant', 'bird', 'competitionClass.event']);

        $this->view('pdf.registration-proof', compact('registration'))
            ->assertSee($registration->registration_number)
            ->assertSee($registration->participant->name)
            ->assertSee($registration->bird->name)
            ->assertSee($registration->bird->bird_type)
            ->assertSee($registration->competitionClass->name)
            ->assertSee($registration->competitionClass->event->name)
            ->assertSee('VERIFIED')
            ->assertSee('17')
            ->assertDontSee($registration->participant->email);
    }
}
