<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\User;

class EventPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->loadMissing('roles')->hasRole(UserRole::Owner) || $user->hasRole(UserRole::Admin);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Event $event): bool
    {
        return $this->manages($user, $event);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->loadMissing('roles')->hasRole(UserRole::Owner);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Event $event): bool
    {
        return $this->manages($user, $event);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Event $event): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Event $event): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Event $event): bool
    {
        return false;
    }

    public function assignAdmin(User $user, Event $event): bool
    {
        return $event->owner_id === $user->id && $user->loadMissing('roles')->hasRole(UserRole::Owner);
    }

    public function publish(User $user, Event $event): bool
    {
        return $this->assignAdmin($user, $event);
    }

    private function manages(User $user, Event $event): bool
    {
        if ($event->owner_id === $user->id && $user->loadMissing('roles')->hasRole(UserRole::Owner)) {
            return true;
        }

        return $user->loadMissing('roles')->hasRole(UserRole::Admin)
            && $event->adminAssignments()->where('user_id', $user->id)->exists();
    }
}
