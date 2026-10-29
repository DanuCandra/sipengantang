<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Bird;
use App\Models\User;

class BirdPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->loadMissing('roles')->hasRole(UserRole::Participant);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Bird $bird): bool
    {
        return $bird->owner_id === $user->id && $this->viewAny($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $this->viewAny($user);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Bird $bird): bool
    {
        return $this->view($user, $bird);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Bird $bird): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Bird $bird): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Bird $bird): bool
    {
        return false;
    }
}
