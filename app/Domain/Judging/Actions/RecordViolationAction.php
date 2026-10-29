<?php

namespace App\Domain\Judging\Actions;

use App\Domain\Judging\Services\JudgingStateGuard;
use App\Domain\Judging\Services\ViolationRuleService;
use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\RegistrationStatus;
use App\Enums\ViolationAction;
use App\Enums\ViolationReasonCode;
use App\Events\ParticipantDisqualified;
use App\Events\WarningCreated;
use App\Models\CompetitionClass;
use App\Models\Registration;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class RecordViolationAction
{
    public function __construct(private ActivityRecorder $activityRecorder, private JudgingStateGuard $stateGuard, private ViolationRuleService $ruleService) {}

    public function execute(User $judge, Registration $registration, ViolationAction $intent, ViolationReasonCode $reasonCode, ?string $reason, ?string $note, bool $confirmed, ?string $ipAddress = null): Violation
    {
        return DB::transaction(function () use ($judge, $registration, $intent, $reasonCode, $reason, $note, $confirmed, $ipAddress): Violation {
            // Same lock order as score submission: class, then registration.
            $competitionClass = CompetitionClass::query()->lockForUpdate()->findOrFail($registration->competition_class_id);
            $locked = Registration::query()->lockForUpdate()->findOrFail($registration->id);
            Gate::forUser($judge)->authorize('create', [Violation::class, $locked]);
            if ($locked->status !== RegistrationStatus::Verified || ! $this->stateGuard->isScoringOpen($competitionClass)) {
                throw ValidationException::withMessages(['violation' => 'Pelanggaran hanya dapat dicatat untuk peserta terverifikasi saat penjurian berlangsung. Peserta DQ tidak dapat diubah lagi.']);
            }

            $hasWarning = $locked->violations()->where('action', ViolationAction::Warning)->exists();
            $action = $this->ruleService->determineAction($intent, $reasonCode, $hasWarning);
            $disqualified = $action === ViolationAction::Disqualification;
            $validated = Validator::make([
                'reason' => $reason === null ? null : trim($reason),
                'note' => $note === null ? null : trim($note),
                'confirm_disqualification' => $confirmed,
            ], [
                'reason' => [Rule::requiredIf($reasonCode === ViolationReasonCode::Other), 'nullable', 'string', 'max:255'],
                'note' => [$disqualified ? 'required' : 'nullable', 'string', 'max:2000'],
                'confirm_disqualification' => [$disqualified ? 'accepted' : 'boolean'],
            ], [
                'reason.required' => 'Jelaskan alasan bila memilih Lainnya.',
                'reason.max' => 'Alasan maksimal 255 karakter.',
                'note.required' => 'Catatan kejadian wajib diisi untuk diskualifikasi, termasuk eskalasi peringatan.',
                'note.max' => 'Catatan maksimal 2.000 karakter.',
                'confirm_disqualification.accepted' => 'Konfirmasi diskualifikasi wajib dicentang. Periksa riwayat terbaru bila peserta sudah mendapat peringatan.',
            ])->validate();

            $violation = $locked->violations()->create([
                'judge_id' => $judge->id, 'action' => $action, 'reason_code' => $reasonCode,
                'reason' => $reasonCode === ViolationReasonCode::Other ? $validated['reason'] : $reasonCode->label(),
                'note' => blank($validated['note']) ? null : $validated['note'],
            ]);
            if ($disqualified) {
                $locked->update(['status' => RegistrationStatus::Disqualified, 'disqualified_at' => now()]);
            }
            $this->activityRecorder->record($judge, $disqualified ? ActivityAction::ParticipantDisqualified : ActivityAction::WarningCreated, $violation, ['registration_id' => $locked->id, 'competition_class_id' => $competitionClass->id, 'escalated' => $intent === ViolationAction::Warning && $disqualified], $ipAddress);

            if ($disqualified) {
                ParticipantDisqualified::dispatch($violation->id, $locked->id, $competitionClass->id);
            } else {
                WarningCreated::dispatch($violation->id, $locked->id, $competitionClass->id);
            }

            return $violation;
        });
    }
}
