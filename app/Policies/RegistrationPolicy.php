<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;

class RegistrationPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        $user->loadMissing('roles');

        return $user->hasRole(UserRole::Participant) || $user->hasRole(UserRole::Admin) || $user->hasRole(UserRole::Owner);
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Registration $registration): bool
    {
        if ($registration->participant_id === $user->id && $user->loadMissing('roles')->hasRole(UserRole::Participant)) {
            return true;
        }

        $competitionClass = CompetitionClass::query()->find($registration->competition_class_id);
        $event = $competitionClass ? Event::query()->find($competitionClass->event_id) : null;

        return $event !== null && $user->can('view', $event);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->loadMissing('roles')->hasRole(UserRole::Participant);
    }

    public function submitPayment(User $user, Registration $registration): bool
    {
        return $registration->participant_id === $user->id
            && $user->loadMissing('roles')->hasRole(UserRole::Participant);
    }

    public function downloadProof(User $user, Registration $registration): bool
    {
        return $this->submitPayment($user, $registration);
    }

    public function chooseGantanganNumber(User $user, Registration $registration): bool
    {
        return $this->submitPayment($user, $registration);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Registration $registration): bool
    {
        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Registration $registration): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Registration $registration): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Registration $registration): bool
    {
        return false;
    }
}
