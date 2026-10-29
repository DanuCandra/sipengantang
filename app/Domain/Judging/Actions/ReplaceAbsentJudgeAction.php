<?php

namespace App\Domain\Judging\Actions;

use App\Domain\Judging\Services\JudgingStateGuard;
use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\UserRole;
use App\Events\JudgingStateChanged;
use App\Jobs\RecalculateProvisionalRanking;
use App\Models\CompetitionClass;
use App\Models\JudgeAssignment;
use App\Models\JudgeEvaluation;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class ReplaceAbsentJudgeAction
{
    public function __construct(private JudgingStateGuard $guard, private ActivityRecorder $audit) {}

    public function execute(User $admin, CompetitionClass $competitionClass, JudgeAssignment $assignment, User $replacement, string $note, ?string $ip = null): JudgeAssignment
    {
        return DB::transaction(function () use ($admin, $competitionClass, $assignment, $replacement, $note, $ip): JudgeAssignment {
            $class = CompetitionClass::query()->lockForUpdate()->findOrFail($competitionClass->id);
            Gate::forUser($admin)->authorize('manageJudging', $class);
            abort_unless($assignment->competition_class_id === $class->id, 404);
            $this->guard->ensureAssignmentsMutable($class);
            $old = JudgeAssignment::query()->lockForUpdate()->findOrFail($assignment->id);
            if ($old->replaced_at !== null || JudgeEvaluation::query()->submitted()->where('judge_id', $old->judge_id)->whereHas('registration', fn ($query) => $query->where('competition_class_id', $class->id))->exists()) {
                throw ValidationException::withMessages(['replacement' => 'Juri sudah diganti atau memiliki nilai submitted. Penggantian tidak boleh menimpa riwayat nilai.']);
            }
            $replacement = User::query()->with('roles')->lockForUpdate()->findOrFail($replacement->id);
            if (! $replacement->isActive() || $class->judgeAssignments()->where('judge_id', $replacement->id)->exists()) {
                throw ValidationException::withMessages(['replacement' => 'Pilih akun aktif yang belum pernah ditugaskan ke kelas ini.']);
            }
            Validator::make(['note' => trim($note)], ['note' => ['required', 'string', 'max:2000']], ['note.required' => 'Alasan penggantian Juri wajib diisi.'])->validate();
            $replacement->roles()->firstOrCreate(['role' => UserRole::Judge]);
            $new = $class->judgeAssignments()->create(['judge_id' => $replacement->id]);
            $old->update(['replaced_at' => now(), 'replacement_assignment_id' => $new->id, 'replacement_note' => trim($note)]);
            $this->audit->record($admin, ActivityAction::JudgeReplaced, $old, ['replacement_assignment_id' => $new->id, 'competition_class_id' => $class->id], $ip);
            RecalculateProvisionalRanking::dispatch($class->id)->afterCommit();
            JudgingStateChanged::dispatch($class->id);

            return $new;
        });
    }
}
