<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\User;

class CompetitionClassPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, CompetitionClass $competitionClass): bool
    {
        $event = Event::query()->find($competitionClass->event_id);

        return $event !== null && $user->can('view', $event);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Event $event): bool
    {
        return $user->can('update', $event);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, CompetitionClass $competitionClass): bool
    {
        $event = Event::query()->find($competitionClass->event_id);

        return $event !== null && $user->can('update', $event);
    }

    public function manageJudging(User $user, CompetitionClass $competitionClass): bool
    {
        return $user->loadMissing('roles')->hasRole(UserRole::Admin)
            && Event::query()->whereKey($competitionClass->event_id)
                ->whereHas('adminAssignments', fn ($query) => $query->where('user_id', $user->id))->exists();
    }

    public function judge(User $user, CompetitionClass $competitionClass): bool
    {
        return $user->loadMissing('roles')->hasRole(UserRole::Judge)
            && $competitionClass->judgeAssignments()->active()->where('judge_id', $user->id)->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, CompetitionClass $competitionClass): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, CompetitionClass $competitionClass): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, CompetitionClass $competitionClass): bool
    {
        return false;
    }
}
