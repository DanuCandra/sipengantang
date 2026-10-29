<?php

namespace App\Policies;

use App\Models\CompetitionClass;
use App\Models\Prize;
use App\Models\User;

class PrizePolicy
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
    public function view(User $user, Prize $prize): bool
    {
        $competitionClass = CompetitionClass::query()->find($prize->competition_class_id);

        return $competitionClass !== null && $user->can('view', $competitionClass);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, CompetitionClass $competitionClass): bool
    {
        return $user->can('update', $competitionClass);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Prize $prize): bool
    {
        $competitionClass = CompetitionClass::query()->find($prize->competition_class_id);

        return $competitionClass !== null && $user->can('update', $competitionClass);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Prize $prize): bool
    {
        return $this->update($user, $prize);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Prize $prize): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Prize $prize): bool
    {
        return false;
    }
}
