<?php

namespace App\Domain\Registration\Actions;

use App\Domain\Registration\Services\GantanganNumberService;
use App\Domain\Registration\Services\RegistrationNumberGenerator;
use App\Domain\Registration\Services\RegistrationQuotaService;
use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\CompetitionClassStatus;
use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Models\Bird;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CreateRegistrationAction
{
    public function __construct(
        private RegistrationQuotaService $quotaService,
        private RegistrationNumberGenerator $numberGenerator,
        private ActivityRecorder $activityRecorder,
        private GantanganNumberService $gantanganNumbers,
    ) {}

    public function execute(User $participant, CompetitionClass $competitionClass, Bird $bird, string $gantanganNumber, ?string $ipAddress = null): Registration
    {
        try {
            return DB::transaction(function () use ($participant, $competitionClass, $bird, $gantanganNumber, $ipAddress): Registration {
                $lockedClass = CompetitionClass::query()->lockForUpdate()->findOrFail($competitionClass->id);
                $event = Event::query()->findOrFail($lockedClass->event_id);

                $this->ensureRegistrationIsOpen($event, $lockedClass);

                if ($bird->owner_id !== $participant->id) {
                    throw ValidationException::withMessages(['bird_id' => 'Burung yang dipilih bukan milik akun Anda.']);
                }

                if (Str::lower(trim($bird->bird_type)) !== Str::lower(trim($lockedClass->bird_type))) {
                    throw ValidationException::withMessages(['bird_id' => 'Jenis burung tidak sesuai dengan kelas yang dipilih.']);
                }

                $alreadyRegistered = Registration::query()
                    ->where('competition_class_id', $lockedClass->id)
                    ->where('bird_id', $bird->id)
                    ->where('status', '!=', RegistrationStatus::Cancelled)
                    ->exists();

                if ($alreadyRegistered) {
                    throw ValidationException::withMessages(['bird_id' => 'Burung ini sudah terdaftar pada kelas tersebut.']);
                }

                if (! $this->quotaService->hasAvailableSlot($lockedClass)) {
                    throw ValidationException::withMessages(['competition_class_id' => 'Kuota kelas sudah penuh.']);
                }

                $number = $this->gantanganNumbers->ensureAvailable($lockedClass, $gantanganNumber);
                $registration = Registration::query()->create([
                    'competition_class_id' => $lockedClass->id,
                    'participant_id' => $participant->id,
                    'bird_id' => $bird->id,
                    'registration_number' => 'TEMP-'.Str::ulid(),
                    'registration_fee_snapshot' => $lockedClass->registration_fee,
                    'gantangan_number' => $number,
                    'status' => RegistrationStatus::PendingPayment,
                    'registered_at' => now(),
                ]);

                $registration->update([
                    'registration_number' => $this->numberGenerator->generate($registration, $event->start_at->year),
                ]);

                $this->activityRecorder->record(
                    $participant,
                    ActivityAction::RegistrationCreated,
                    $registration,
                    ['competition_class_id' => $lockedClass->id, 'bird_id' => $bird->id, 'gantangan_number' => $number],
                    $ipAddress,
                );

                return $registration->refresh()->load(['competitionClass.event', 'bird']);
            });
        } catch (UniqueConstraintViolationException $exception) {
            if (! str_contains($exception->getMessage(), 'registrations_class_gantangan_unique')) {
                throw $exception;
            }
            throw ValidationException::withMessages(['gantangan_number' => GantanganNumberService::conflictMessage()]);
        }
    }

    private function ensureRegistrationIsOpen(Event $event, CompetitionClass $competitionClass): void
    {
        if ($event->status !== EventStatus::Upcoming || $event->published_at === null) {
            throw ValidationException::withMessages(['competition_class_id' => 'Event ini belum membuka pendaftaran.']);
        }

        if ($competitionClass->status !== CompetitionClassStatus::Upcoming) {
            throw ValidationException::withMessages(['competition_class_id' => 'Kelas ini tidak sedang menerima pendaftaran.']);
        }

        if ($event->registration_open_at?->isFuture()) {
            throw ValidationException::withMessages(['competition_class_id' => 'Periode pendaftaran belum dimulai.']);
        }

        if ($event->registration_close_at?->isPast()) {
            throw ValidationException::withMessages(['competition_class_id' => 'Periode pendaftaran sudah ditutup.']);
        }
    }
}
