<?php

namespace App\Domain\Judging\Actions;

use App\Domain\Judging\Services\JudgingStateGuard;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\CompetitionClass;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class CreateAndAssignJudgeAction
{
    public function __construct(private AssignJudgeAction $assignJudge, private JudgingStateGuard $stateGuard) {}

    /** @param array{name: string, email: string, password: string, phone?: ?string} $data */
    public function execute(User $admin, CompetitionClass $competitionClass, array $data, ?string $ipAddress = null): User
    {
        return DB::transaction(function () use ($admin, $competitionClass, $data, $ipAddress): User {
            $lockedClass = CompetitionClass::query()->lockForUpdate()->findOrFail($competitionClass->id);
            Gate::forUser($admin)->authorize('manageJudging', $lockedClass);
            $this->stateGuard->ensureAssignmentsMutable($lockedClass);
            $judge = User::query()->create([
                'name' => $data['name'], 'email' => $data['email'], 'password' => $data['password'],
                'phone' => $data['phone'] ?? null, 'status' => UserStatus::Active,
            ]);
            $judge->roles()->create(['role' => UserRole::Judge]);
            $this->assignJudge->execute($admin, $lockedClass, $judge, $ipAddress);

            return $judge;
        });
    }
}
