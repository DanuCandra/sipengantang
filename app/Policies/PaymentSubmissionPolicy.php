<?php

namespace App\Policies;

use App\Enums\UserRole;
use App\Models\Event;
use App\Models\PaymentSubmission;
use App\Models\User;

class PaymentSubmissionPolicy
{
    public function view(User $user, PaymentSubmission $paymentSubmission): bool
    {
        $paymentSubmission->loadMissing('registration.competitionClass.event');
        $registration = $paymentSubmission->registration;

        return ($registration->participant_id === $user->id && $user->loadMissing('roles')->hasRole(UserRole::Participant))
            || $user->can('view', $registration->competitionClass->event);
    }

    public function review(User $user, PaymentSubmission $paymentSubmission): bool
    {
        $paymentSubmission->loadMissing('registration.competitionClass.event');

        return $this->viewAny($user, $paymentSubmission->registration->competitionClass->event);
    }

    public function viewAny(User $user, Event $event): bool
    {
        return $user->loadMissing('roles')->hasRole(UserRole::Admin)
            && $event->adminAssignments()->where('user_id', $user->id)->exists();
    }
}
