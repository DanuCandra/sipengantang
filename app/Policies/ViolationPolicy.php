<?php

namespace App\Policies;

use App\Models\CompetitionClass;
use App\Models\Registration;
use App\Models\User;
use App\Models\Violation;

class ViolationPolicy
{
    public function create(User $user, Registration $registration): bool
    {
        $competitionClass = CompetitionClass::query()->find($registration->competition_class_id);

        return $competitionClass !== null && $user->can('judge', $competitionClass);
    }

    public function viewAny(User $user, Registration $registration): bool
    {
        $competitionClass = CompetitionClass::query()->with('event')->find($registration->competition_class_id);

        return $competitionClass !== null
            && ($user->can('judge', $competitionClass) || $user->can('view', $competitionClass->event));
    }

    public function view(User $user, Violation $violation): bool
    {
        $registration = Registration::query()->find($violation->registration_id);

        return $registration !== null && $this->viewAny($user, $registration);
    }
}
