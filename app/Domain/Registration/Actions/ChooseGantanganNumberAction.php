<?php

namespace App\Domain\Registration\Actions;

use App\Domain\Registration\Services\GantanganNumberService;
use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\CompetitionClassStatus;
use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Models\CompetitionClass;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class ChooseGantanganNumberAction
{
    public function __construct(private GantanganNumberService $numbers, private ActivityRecorder $audit) {}

    public function execute(User $participant, Registration $registration, string $number, ?string $ipAddress = null): void
    {
        try {
            DB::transaction(function () use ($participant, $registration, $number, $ipAddress): void {
                $class = CompetitionClass::query()->lockForUpdate()->findOrFail($registration->competition_class_id);
                $locked = Registration::query()->lockForUpdate()->findOrFail($registration->id);
                Gate::forUser($participant)->authorize('chooseGantanganNumber', $locked);
                $class->load('event');
                if ($locked->gantangan_number !== null || $class->status !== CompetitionClassStatus::Upcoming
                    || $class->event->status !== EventStatus::Upcoming || ! $class->event->published_at
                    || ! in_array($locked->status, [RegistrationStatus::PendingPayment, RegistrationStatus::WaitingVerification, RegistrationStatus::PaymentRejected, RegistrationStatus::Verified], true)) {
                    throw ValidationException::withMessages(['gantangan_number' => 'Nomor hanya dapat dipilih sekali untuk pendaftaran yang belum memiliki nomor, sebelum penjurian dimulai.']);
                }
                $number = $this->numbers->ensureAvailable($class, $number);
                $locked->update(['gantangan_number' => $number]);
                $this->audit->record($participant, ActivityAction::GantanganNumberChosen, $locked, ['competition_class_id' => $class->id, 'gantangan_number' => $number], $ipAddress);
            });
        } catch (UniqueConstraintViolationException $exception) {
            if (! str_contains($exception->getMessage(), 'registrations_class_gantangan_unique')) {
                throw $exception;
            }
            throw ValidationException::withMessages(['gantangan_number' => GantanganNumberService::conflictMessage()]);
        }
    }
}
