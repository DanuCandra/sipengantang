<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Services\PaymentStateGuard;
use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Events\PaymentRejected;
use App\Events\PaymentVerified;
use App\Models\CompetitionClass;
use App\Models\PaymentSubmission;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ReviewPaymentAction
{
    public function __construct(private ActivityRecorder $activityRecorder, private PaymentStateGuard $stateGuard) {}

    public function execute(User $admin, PaymentSubmission $payment, PaymentStatus $decision, ?string $reason = null, ?string $ipAddress = null): void
    {
        if ($decision === PaymentStatus::WaitingVerification || ($decision === PaymentStatus::Rejected && blank($reason))) {
            throw ValidationException::withMessages(['rejection_reason' => 'Alasan penolakan wajib diisi.']);
        }

        DB::transaction(function () use ($admin, $payment, $decision, $reason, $ipAddress): void {
            $registration = Registration::query()->findOrFail($payment->registration_id);
            $competitionClass = CompetitionClass::query()->lockForUpdate()->findOrFail($registration->competition_class_id);
            $lockedRegistration = Registration::query()->lockForUpdate()->findOrFail($registration->id);
            $lockedPayment = PaymentSubmission::query()->lockForUpdate()->findOrFail($payment->id);
            Gate::forUser($admin)->authorize('review', $lockedPayment);
            $this->stateGuard->ensureMutable($competitionClass);

            if ($lockedPayment->status !== PaymentStatus::WaitingVerification
                || $lockedRegistration->status !== RegistrationStatus::WaitingVerification
                || $lockedRegistration->paymentSubmissions()->latest('id')->value('id') !== $lockedPayment->id) {
                throw ValidationException::withMessages(['payment' => 'Bukti ini sudah ditinjau atau bukan percobaan pembayaran aktif. Muat ulang halaman.']);
            }

            $verified = $decision === PaymentStatus::Verified;
            $lockedPayment->update([
                'status' => $decision,
                'reviewed_by' => $admin->id,
                'reviewed_at' => now(),
                'rejection_reason' => $verified ? null : trim($reason),
            ]);
            $lockedRegistration->update([
                'status' => $verified ? RegistrationStatus::Verified : RegistrationStatus::PaymentRejected,
                'verified_at' => $verified ? now() : null,
            ]);

            $this->activityRecorder->record($admin, $verified ? ActivityAction::PaymentVerified : ActivityAction::PaymentRejected, $lockedPayment, ['registration_id' => $lockedRegistration->id], $ipAddress);

            if ($verified) {
                PaymentVerified::dispatch($lockedPayment->id, $lockedRegistration->id);
            } else {
                PaymentRejected::dispatch($lockedPayment->id, $lockedRegistration->id);
            }
        });
    }
}
