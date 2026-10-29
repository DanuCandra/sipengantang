<?php

namespace Tests\Feature\Payment;

use App\Domain\Payment\Actions\ReviewPaymentAction;
use App\Enums\ActivityAction;
use App\Enums\CompetitionClassStatus;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Events\PaymentRejected;
use App\Events\PaymentSubmitted;
use App\Events\PaymentVerified;
use App\Models\ActivityLog;
use App\Models\EventAdmin;
use App\Models\PaymentSubmission;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class PaymentWorkflowTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_upload_stores_private_proof_and_synchronizes_registration_without_accepting_state_fields(): void
    {
        Storage::fake('private');
        Storage::fake('public');
        $registration = Registration::factory()->create();
        $participant = $registration->participant()->firstOrFail();
        Event::fake([PaymentSubmitted::class]);
        $this->freezeTime();

        $this->actingAs($participant)->post(route('participant.registrations.payments.store', $registration), [
            'amount' => 150000,
            'proof' => UploadedFile::fake()->image('transfer.jpg'),
            'status' => PaymentStatus::Verified->value,
            'reviewed_by' => $participant->id,
        ])->assertRedirect(route('participant.registrations.show', $registration));

        $payment = PaymentSubmission::query()->sole();
        $this->assertSame(PaymentStatus::WaitingVerification, $payment->status);
        $this->assertSame(150000, $payment->amount);
        $this->assertNull($payment->reviewed_by);
        $this->assertSame(RegistrationStatus::WaitingVerification, $registration->refresh()->status);
        Storage::disk('private')->assertExists($payment->proof_path);
        Storage::disk('public')->assertMissing($payment->proof_path);
        $this->assertStringNotContainsString('transfer.jpg', $payment->proof_path);
        $this->assertDatabaseHas('activity_logs', ['action' => ActivityAction::PaymentSubmitted->value, 'subject_id' => $payment->id]);
        Event::assertDispatched(PaymentSubmitted::class, fn ($event) => $event->paymentSubmissionId === $payment->id && $event->registrationId === $registration->id);
        $this->actingAs($participant)->get(route('participant.registrations.show', $registration))
            ->assertSee('Menunggu verifikasi')
            ->assertDontSee($payment->proof_path);
    }

    public function test_assigned_admin_verifies_payment_and_sets_verified_timestamp(): void
    {
        [$admin, $event, $payment] = $this->reviewFixture();
        $this->travelTo(now()->startOfSecond());
        Event::fake([PaymentVerified::class]);

        $this->actingAs($admin)->patch(route('admin.events.payments.update', [$event, $payment]), [
            'decision' => PaymentStatus::Verified->value,
        ])->assertRedirect(route('admin.events.payments.show', [$event, $payment]));

        $this->assertSame(PaymentStatus::Verified, $payment->refresh()->status);
        $this->assertSame($admin->id, $payment->reviewed_by);
        $this->assertTrue($payment->reviewed_at->equalTo(now()));
        $registration = $payment->registration()->firstOrFail();
        $this->assertSame(RegistrationStatus::Verified, $registration->status);
        $this->assertTrue($registration->verified_at->equalTo(now()));
        $this->assertDatabaseHas('activity_logs', ['action' => ActivityAction::PaymentVerified->value, 'user_id' => $admin->id]);
        Event::assertDispatched(PaymentVerified::class, fn ($event) => $event->paymentSubmissionId === $payment->id);
    }

    public function test_rejected_attempt_and_file_remain_when_participant_uploads_new_proof(): void
    {
        Storage::fake('private');
        [$admin, $event, $payment] = $this->reviewFixture();
        Storage::disk('private')->put($payment->proof_path, 'original proof');
        $registration = $payment->registration()->with('participant')->firstOrFail();
        Event::fake([PaymentRejected::class, PaymentSubmitted::class]);

        $this->actingAs($admin)->patch(route('admin.events.payments.update', [$event, $payment]), [
            'decision' => PaymentStatus::Rejected->value,
            'rejection_reason' => 'Bukti transfer tidak terbaca.',
        ])->assertRedirect();

        $this->assertSame(RegistrationStatus::PaymentRejected, $registration->refresh()->status);
        Event::assertDispatched(PaymentRejected::class);
        $this->actingAs($registration->participant)->get(route('participant.registrations.show', $registration))
            ->assertSee('Bukti transfer tidak terbaca.')
            ->assertSee('Kirim bukti baru');

        $this->actingAs($registration->participant)->post(route('participant.registrations.payments.store', $registration), [
            'amount' => 150000,
            'proof' => UploadedFile::fake()->image('new-proof.png'),
        ])->assertRedirect();

        $this->assertDatabaseCount('payment_submissions', 2);
        $this->assertSame(PaymentStatus::Rejected, $payment->refresh()->status);
        $this->assertSame('Bukti transfer tidak terbaca.', $payment->rejection_reason);
        $this->assertSame($admin->id, $payment->reviewed_by);
        Storage::disk('private')->assertExists($payment->proof_path);
        $this->assertSame(RegistrationStatus::WaitingVerification, $registration->refresh()->status);
        $this->assertDatabaseHas('activity_logs', ['action' => ActivityAction::PaymentRejected->value]);
        Event::assertDispatched(PaymentSubmitted::class);
    }

    public function test_second_upload_is_rejected_while_payment_is_waiting_and_new_file_is_cleaned_up(): void
    {
        Storage::fake('private');
        $payment = PaymentSubmission::factory()->create();
        Storage::disk('private')->put($payment->proof_path, 'old proof');
        $registration = $payment->registration()->with('participant')->firstOrFail();
        Event::fake([PaymentSubmitted::class]);

        $this->actingAs($registration->participant)->post(route('participant.registrations.payments.store', $registration), [
            'amount' => 150000,
            'proof' => UploadedFile::fake()->image('duplicate.jpg'),
        ])->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('payment_submissions', 1);
        $this->assertSame([$payment->proof_path], Storage::disk('private')->allFiles());
        Event::assertNotDispatched(PaymentSubmitted::class);
    }

    public function test_second_review_does_not_overwrite_first_decision_or_duplicate_audit(): void
    {
        [$admin, $event, $payment] = $this->reviewFixture();
        $this->actingAs($admin)->patch(route('admin.events.payments.update', [$event, $payment]), ['decision' => PaymentStatus::Verified->value])->assertRedirect();

        $this->actingAs($admin)->patch(route('admin.events.payments.update', [$event, $payment]), [
            'decision' => PaymentStatus::Rejected->value,
            'rejection_reason' => 'Keputusan terlambat',
        ])->assertSessionHasErrors('payment');

        $this->assertSame(PaymentStatus::Verified, $payment->refresh()->status);
        $this->assertNull($payment->rejection_reason);
        $this->assertSame(RegistrationStatus::Verified, $payment->registration()->firstOrFail()->status);
        $this->assertSame(1, ActivityLog::query()->where('action', ActivityAction::PaymentVerified)->count());
        $this->assertDatabaseMissing('activity_logs', ['action' => ActivityAction::PaymentRejected->value]);
    }

    public function test_review_rollback_restores_both_states_and_does_not_dispatch_event(): void
    {
        [$admin, , $payment] = $this->reviewFixture();
        Event::fake([PaymentVerified::class]);

        try {
            DB::transaction(function () use ($admin, $payment): void {
                $this->app->make(ReviewPaymentAction::class)->execute($admin, $payment, PaymentStatus::Verified);
                Event::assertNotDispatched(PaymentVerified::class);
                throw new RuntimeException('Simulated transaction failure');
            });
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated transaction failure', $exception->getMessage());
        }

        $this->assertSame(PaymentStatus::WaitingVerification, $payment->refresh()->status);
        $registration = $payment->registration()->firstOrFail();
        $this->assertSame(RegistrationStatus::WaitingVerification, $registration->status);
        $this->assertNull($registration->verified_at);
        $this->assertNull($payment->reviewed_by);
        $this->assertDatabaseCount('activity_logs', 0);
        Event::assertNotDispatched(PaymentVerified::class);
    }

    #[DataProvider('terminalRegistrationStates')]
    public function test_closed_registration_does_not_accept_new_proof(RegistrationStatus $status): void
    {
        Storage::fake('private');
        $registration = Registration::factory()->create(['status' => $status]);

        $this->actingAs($registration->participant()->firstOrFail())->post(route('participant.registrations.payments.store', $registration), [
            'amount' => 150000,
            'proof' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertSessionHasErrors('payment');

        $this->assertDatabaseCount('payment_submissions', 0);
        $this->assertSame([], Storage::disk('private')->allFiles());
        $this->assertSame($status, $registration->refresh()->status);
    }

    public static function terminalRegistrationStates(): array
    {
        return ['verified' => [RegistrationStatus::Verified], 'cancelled' => [RegistrationStatus::Cancelled], 'disqualified' => [RegistrationStatus::Disqualified]];
    }

    public function test_rejection_requires_a_reason_and_preserves_waiting_state_on_failure(): void
    {
        [$admin, $event, $payment] = $this->reviewFixture();

        $this->actingAs($admin)->patch(route('admin.events.payments.update', [$event, $payment]), ['decision' => PaymentStatus::Rejected->value])
            ->assertSessionHasErrors(['rejection_reason' => 'Alasan penolakan wajib diisi.']);

        $this->assertSame(PaymentStatus::WaitingVerification, $payment->refresh()->status);
        $this->assertSame(RegistrationStatus::WaitingVerification, $payment->registration()->firstOrFail()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_previously_rejected_attempt_cannot_be_verified_after_new_upload(): void
    {
        [$admin, $event, $payment] = $this->reviewFixture();
        $payment->update(['status' => PaymentStatus::Rejected, 'rejection_reason' => 'Bukti tidak terbaca.', 'reviewed_by' => $admin->id, 'reviewed_at' => now()]);
        $newPayment = PaymentSubmission::factory()->create(['registration_id' => $payment->registration_id]);

        $this->actingAs($admin)->patch(route('admin.events.payments.update', [$event, $payment]), ['decision' => PaymentStatus::Verified->value])
            ->assertSessionHasErrors('payment');

        $this->assertSame(PaymentStatus::Rejected, $payment->refresh()->status);
        $this->assertSame(PaymentStatus::WaitingVerification, $newPayment->refresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_upload_requires_proof_and_amount_before_any_file_or_database_write(): void
    {
        Storage::fake('private');
        $registration = Registration::factory()->create();

        $this->actingAs($registration->participant()->firstOrFail())->post(route('participant.registrations.payments.store', $registration), [])
            ->assertSessionHasErrors(['proof' => 'Bukti pembayaran wajib diunggah.', 'amount' => 'Nominal transfer wajib diisi.']);

        $this->assertDatabaseCount('payment_submissions', 0);
        $this->assertSame([], Storage::disk('private')->allFiles());
    }

    public function test_finalized_class_cannot_be_changed_by_payment_review(): void
    {
        [$admin, $event, $payment] = $this->reviewFixture();
        $payment->registration()->firstOrFail()->competitionClass()->update(['status' => CompetitionClassStatus::Finalized]);

        $this->actingAs($admin)->patch(route('admin.events.payments.update', [$event, $payment]), ['decision' => PaymentStatus::Verified->value])
            ->assertSessionHasErrors('payment');

        $this->assertSame(PaymentStatus::WaitingVerification, $payment->refresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    #[DataProvider('invalidAmounts')]
    public function test_upload_rejects_invalid_amounts(mixed $amount): void
    {
        Storage::fake('private');
        $registration = Registration::factory()->create();

        $this->actingAs($registration->participant()->firstOrFail())->post(route('participant.registrations.payments.store', $registration), [
            'amount' => $amount,
            'proof' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertSessionHasErrors('amount');

        $this->assertDatabaseCount('payment_submissions', 0);
        $this->assertSame([], Storage::disk('private')->allFiles());
    }

    public static function invalidAmounts(): array
    {
        return ['missing' => [null], 'zero' => [0], 'negative' => [-1], 'fractional' => [150000.5], 'overflow' => ['1000000000000']];
    }

    public function test_proof_must_be_safe_image_no_larger_than_five_megabytes(): void
    {
        Storage::fake('private');
        $registration = Registration::factory()->create();
        $this->actingAs($registration->participant()->firstOrFail());

        foreach ([UploadedFile::fake()->create('script.php', 2, 'text/x-php'), UploadedFile::fake()->create('proof.pdf', 10, 'application/pdf'), UploadedFile::fake()->image('large.jpg')->size(5121)] as $file) {
            $this->post(route('participant.registrations.payments.store', $registration), ['amount' => 150000, 'proof' => $file])->assertSessionHasErrors('proof');
        }

        $this->assertDatabaseCount('payment_submissions', 0);
        $this->assertSame([], Storage::disk('private')->allFiles());
    }

    /** @return array{User, \App\Models\Event, PaymentSubmission} */
    private function reviewFixture(): array
    {
        $payment = PaymentSubmission::factory()->create();
        $registration = $payment->registration()->with('competitionClass.event')->firstOrFail();
        $event = $registration->competitionClass->event;
        $admin = User::factory()->admin()->create();
        EventAdmin::factory()->for($event)->for($admin, 'user')->create();

        return [$admin, $event, $payment];
    }
}
