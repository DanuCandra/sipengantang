<?php

namespace App\Domain\Judging\Actions;

use App\Domain\Judging\Services\EvaluationScoreValidator;
use App\Domain\Judging\Services\JudgingStateGuard;
use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\EvaluationStatus;
use App\Enums\RegistrationStatus;
use App\Events\JudgeScoreSubmitted;
use App\Models\CompetitionClass;
use App\Models\JudgeEvaluation;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class SaveEvaluationAction
{
    public function __construct(private ActivityRecorder $activityRecorder, private EvaluationScoreValidator $scoreValidator, private JudgingStateGuard $stateGuard) {}

    /** @param array<int|string, mixed> $scores */
    public function execute(User $judge, Registration $registration, array $scores, EvaluationStatus $intent, ?string $ipAddress = null): JudgeEvaluation
    {
        return DB::transaction(function () use ($judge, $registration, $scores, $intent, $ipAddress): JudgeEvaluation {
            $competitionClass = CompetitionClass::query()->lockForUpdate()->findOrFail($registration->competition_class_id);
            $lockedRegistration = Registration::query()->lockForUpdate()->findOrFail($registration->id);
            Gate::forUser($judge)->authorize('score', [JudgeEvaluation::class, $lockedRegistration]);
            if (! $this->stateGuard->isScoringOpen($competitionClass) || $lockedRegistration->status !== RegistrationStatus::Verified) {
                throw ValidationException::withMessages(['judging' => 'Penilaian hanya tersedia untuk peserta terverifikasi saat penjurian kelas berlangsung.']);
            }

            $evaluation = JudgeEvaluation::query()->where('registration_id', $lockedRegistration->id)->where('judge_id', $judge->id)->lockForUpdate()->first();
            if ($evaluation?->status === EvaluationStatus::Submitted) {
                throw ValidationException::withMessages(['judging' => 'Nilai sudah dikirim dan tidak dapat diubah.']);
            }
            $values = $this->scoreValidator->validate($competitionClass, $scores, $intent);
            $evaluation ??= new JudgeEvaluation(['registration_id' => $lockedRegistration->id, 'judge_id' => $judge->id]);
            $evaluation->fill(['status' => $intent, 'submitted_at' => $intent === EvaluationStatus::Submitted ? now() : null])->save();
            // Only mutable draft rows reach this point; submitted score history is never replaced.
            $evaluation->scores()->delete();
            $evaluation->scores()->createMany(collect($values)->map(fn ($score, $id) => ['criterion_id' => $id, 'score' => $score])->values()->all());
            $this->activityRecorder->record($judge, $intent === EvaluationStatus::Submitted ? ActivityAction::ScoreSubmitted : ActivityAction::ScoreDraftSaved, $evaluation, ['competition_class_id' => $competitionClass->id], $ipAddress);

            if ($intent === EvaluationStatus::Submitted) {
                JudgeScoreSubmitted::dispatch($evaluation->id, $lockedRegistration->id, $competitionClass->id);
            }

            return $evaluation;
        });
    }
}
