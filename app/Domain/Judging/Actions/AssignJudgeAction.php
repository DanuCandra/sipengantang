<?php

namespace App\Domain\Judging\Actions;

use App\Domain\Judging\Services\JudgingStateGuard;
use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\UserRole;
use App\Events\JudgingStateChanged;
use App\Models\CompetitionClass;
use App\Models\JudgeAssignment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AssignJudgeAction
{
    public function __construct(private ActivityRecorder $activityRecorder, private JudgingStateGuard $stateGuard) {}

    public function execute(User $admin, CompetitionClass $competitionClass, User $judge, ?string $ipAddress = null): JudgeAssignment
    {
        return DB::transaction(function () use ($admin, $competitionClass, $judge, $ipAddress): JudgeAssignment {
            $lockedClass = CompetitionClass::query()->lockForUpdate()->findOrFail($competitionClass->id);
            Gate::forUser($admin)->authorize('manageJudging', $lockedClass);
            $this->stateGuard->ensureAssignmentsMutable($lockedClass);
            $judge = User::query()->with('roles')->lockForUpdate()->findOrFail($judge->id);
            if (! $judge->isActive()) {
                throw ValidationException::withMessages(['email' => 'Akun yang ditugaskan sebagai Juri harus aktif.']);
            }

            $roleAdded = ! $judge->hasRole(UserRole::Judge);
            $judge->roles()->firstOrCreate(['role' => UserRole::Judge]);

            $assignment = $lockedClass->judgeAssignments()->firstOrCreate(['judge_id' => $judge->id]);
            if ($assignment->replaced_at !== null) {
                throw ValidationException::withMessages(['email' => 'Penugasan lama Juri ini telah diganti dan tidak dapat diaktifkan ulang.']);
            }
            if ($assignment->wasRecentlyCreated) {
                JudgingStateChanged::dispatch($lockedClass->id);
                $this->activityRecorder->record($admin, ActivityAction::JudgeAssigned, $assignment, ['competition_class_id' => $lockedClass->id, 'judge_id' => $judge->id, 'judge_role_added' => $roleAdded], $ipAddress);
            }

            return $assignment;
        });
    }
}
