<?php

namespace Tests\Feature\Payment;

use App\Enums\PaymentStatus;
use App\Models\Event;
use App\Models\EventAdmin;
use App\Models\PaymentSubmission;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PaymentAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_guest_cannot_upload_view_proof_or_review_payments(): void
    {
        $payment = PaymentSubmission::factory()->create();
        $registration = $payment->registration()->with('competitionClass.event')->firstOrFail();

        $this->get(route('payments.proof', $payment))->assertRedirect(route('login'));
        $this->post(route('participant.registrations.payments.store', $registration))->assertRedirect(route('login'));
        $this->get(route('admin.events.payments.index', $registration->competitionClass->event))->assertRedirect(route('login'));
    }

    public function test_other_participant_cannot_upload_to_or_read_private_proof_of_registration(): void
    {
        Storage::fake('private');
        $payment = PaymentSubmission::factory()->create();
        $participant = User::factory()->participant()->create();
        $registration = $payment->registration()->firstOrFail();

        $this->actingAs($participant)->get(route('payments.proof', $payment))->assertForbidden();
        $this->actingAs($participant)->post(route('participant.registrations.payments.store', $registration), [
            'amount' => 150000,
            'proof' => UploadedFile::fake()->image('proof.jpg'),
        ])->assertForbidden();

        $this->assertDatabaseCount('payment_submissions', 1);
        $this->assertSame([], Storage::disk('private')->allFiles());
    }

    public function test_unassigned_admin_cannot_list_review_or_read_payment_proof(): void
    {
        $payment = PaymentSubmission::factory()->create();
        $registration = $payment->registration()->with('competitionClass.event')->firstOrFail();
        $event = $registration->competitionClass->event;
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.events.payments.index', $event))->assertForbidden();
        $this->actingAs($admin)->get(route('payments.proof', $payment))->assertForbidden();
        $this->actingAs($admin)->patch(route('admin.events.payments.update', [$event, $payment]), ['decision' => PaymentStatus::Verified->value])->assertForbidden();

        $this->assertSame(PaymentStatus::WaitingVerification, $payment->refresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_cross_event_payment_cannot_be_opened_or_reviewed_by_assigned_admin(): void
    {
        $event = Event::factory()->create();
        $payment = PaymentSubmission::factory()->create();
        $admin = User::factory()->admin()->create();
        EventAdmin::factory()->for($event)->for($admin, 'user')->create();

        $this->actingAs($admin)->get(route('admin.events.payments.show', [$event, $payment]))->assertNotFound();
        $this->actingAs($admin)->patch(route('admin.events.payments.update', [$event, $payment]), ['decision' => PaymentStatus::Verified->value])->assertForbidden();

        $this->assertSame(PaymentStatus::WaitingVerification, $payment->refresh()->status);
    }

    public function test_proof_is_served_only_to_its_participant_or_event_managers_with_private_cache_headers(): void
    {
        Storage::fake('private');
        $payment = PaymentSubmission::factory()->create();
        $registration = $payment->registration()->with(['competitionClass.event', 'participant'])->firstOrFail();
        $event = $registration->competitionClass->event;
        $admin = User::factory()->admin()->create();
        EventAdmin::factory()->for($event)->for($admin, 'user')->create();
        Storage::disk('private')->put($payment->proof_path, UploadedFile::fake()->image('proof.jpg')->getContent());

        $response = $this->actingAs($registration->participant)->get(route('payments.proof', $payment));
        $response->assertOk()->assertHeader('Content-Type', 'image/jpeg')->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertTrue($response->headers->hasCacheControlDirective('no-store'));
        $this->assertStringNotContainsString('/storage/', $response->getContent());

        $this->actingAs($admin)->get(route('payments.proof', $payment))->assertOk();
        $this->actingAs($event->owner()->firstOrFail())->get(route('payments.proof', $payment))->assertOk();
    }

    public function test_payment_policy_enforces_read_and_review_role_assignment_matrix(): void
    {
        $payment = PaymentSubmission::factory()->create();
        $registration = $payment->registration()->with('competitionClass.event')->firstOrFail();
        $event = $registration->competitionClass->event;
        $assigned = User::factory()->admin()->create();
        EventAdmin::factory()->for($event)->for($assigned, 'user')->create();
        $actors = [
            [$registration->participant()->firstOrFail(), true, false],
            [$event->owner()->firstOrFail(), true, false],
            [$assigned, true, true],
            [User::factory()->admin()->create(), false, false],
            [User::factory()->judge()->create(), false, false],
            [User::factory()->participant()->create(), false, false],
        ];

        foreach ($actors as [$actor, $canRead, $canReview]) {
            $this->assertSame($canRead, Gate::forUser($actor)->allows('view', $payment));
            $this->assertSame($canReview, Gate::forUser($actor)->allows('review', $payment));
        }
    }

    public function test_admin_queue_is_event_scoped_and_supports_status_filter(): void
    {
        $payment = PaymentSubmission::factory()->create();
        $registration = $payment->registration()->with('competitionClass.event')->firstOrFail();
        $event = $registration->competitionClass->event;
        $other = PaymentSubmission::factory()->create();
        $admin = User::factory()->admin()->create();
        EventAdmin::factory()->for($event)->for($admin, 'user')->create();

        $this->actingAs($admin)->get(route('admin.events.payments.index', $event))
            ->assertSee($registration->registration_number, false)
            ->assertDontSee($other->registration()->value('registration_number'));
        $this->actingAs($admin)->get(route('admin.events.payments.show', [$event, $payment]))->assertSee('Verifikasi pembayaran');
        $this->actingAs($admin)->get(route('admin.events.payments.index', [$event, 'status' => PaymentStatus::Rejected->value]))
            ->assertSee('Tidak ada pembayaran pada status ini');
    }

    public function test_rejection_reason_is_escaped_on_participant_and_admin_pages(): void
    {
        $registration = Registration::factory()->create();
        $reason = '<script>alert("xss")</script>';
        $payment = PaymentSubmission::factory()->for($registration)->create(['status' => PaymentStatus::Rejected, 'rejection_reason' => $reason]);

        $this->actingAs($registration->participant()->firstOrFail())->get(route('participant.registrations.show', $registration))
            ->assertSee($reason)->assertDontSee($reason, false);

        $event = $registration->competitionClass()->firstOrFail()->event()->firstOrFail();
        $admin = User::factory()->admin()->create();
        EventAdmin::factory()->for($event)->for($admin, 'user')->create();
        $this->actingAs($admin)->get(route('admin.events.payments.show', [$event, $payment]))
            ->assertSee($reason)->assertDontSee($reason, false);
    }

    public function test_missing_private_file_returns_not_found_to_authorized_participant(): void
    {
        Storage::fake('private');
        $payment = PaymentSubmission::factory()->create();
        $participant = $payment->registration()->firstOrFail()->participant()->firstOrFail();

        $this->actingAs($participant)->get(route('payments.proof', $payment))->assertNotFound();
    }
}
