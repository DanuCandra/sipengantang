<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Services\PaymentStateGuard;
use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Events\PaymentSubmitted;
use App\Models\CompetitionClass;
use App\Models\PaymentSubmission;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Throwable;

class SubmitPaymentAction
{
    public function __construct(private ActivityRecorder $activityRecorder, private PaymentStateGuard $stateGuard) {}

    public function execute(User $participant, Registration $registration, int $amount, UploadedFile $proof, ?string $ipAddress = null): PaymentSubmission
    {
        Gate::forUser($participant)->authorize('submitPayment', $registration);
        $path = $proof->store('payments/'.$registration->id, 'private');

        try {
            $payment = DB::transaction(function () use ($participant, $registration, $amount, $path, $ipAddress): PaymentSubmission {
                // Keep the lock order consistent across payment actions: class, registration, payment.
                $competitionClass = CompetitionClass::query()->lockForUpdate()->findOrFail($registration->competition_class_id);
                $locked = Registration::query()->lockForUpdate()->findOrFail($registration->id);
                Gate::forUser($participant)->authorize('submitPayment', $locked);
                $this->stateGuard->ensureMutable($competitionClass);

                if (! in_array($locked->status, [RegistrationStatus::PendingPayment, RegistrationStatus::PaymentRejected], true)
                    || $locked->paymentSubmissions()->whereIn('status', [PaymentStatus::WaitingVerification, PaymentStatus::Verified])->exists()) {
                    throw ValidationException::withMessages(['payment' => 'Pendaftaran ini tidak menerima bukti baru. Periksa status pembayaran terbaru.']);
                }

                $payment = $locked->paymentSubmissions()->create([
                    'amount' => $amount,
                    'proof_path' => $path,
                    'status' => PaymentStatus::WaitingVerification,
                    'uploaded_at' => now(),
                ]);

                $locked->update(['status' => RegistrationStatus::WaitingVerification]);
                $this->activityRecorder->record($participant, ActivityAction::PaymentSubmitted, $payment, ['registration_id' => $locked->id], $ipAddress);

                return $payment;
            });
        } catch (Throwable $exception) {
            Storage::disk('private')->delete($path);

            throw $exception;
        }

        PaymentSubmitted::dispatch($payment->id, $registration->id);

        return $payment;
    }
}
