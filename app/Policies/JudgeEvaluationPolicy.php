<?php

namespace App\Policies;

use App\Models\CompetitionClass;
use App\Models\JudgeEvaluation;
use App\Models\Registration;
use App\Models\User;

class JudgeEvaluationPolicy
{
    public function score(User $user, Registration $registration): bool
    {
        $competitionClass = CompetitionClass::query()->find($registration->competition_class_id);

        return $competitionClass !== null && $user->can('judge', $competitionClass);
    }

    public function view(User $user, JudgeEvaluation $evaluation): bool
    {
        $registration = Registration::query()->find($evaluation->registration_id);

        return $evaluation->judge_id === $user->id && $registration !== null && $this->score($user, $registration);
    }
}
