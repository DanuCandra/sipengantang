<?php

namespace App\Policies;

use App\Models\RankingRun;
use App\Models\User;

class RankingRunPolicy
{
    public function view(User $user, RankingRun $run): bool
    {
        $run->loadMissing('competitionClass.event');

        return $user->can('view', $run->competitionClass->event);
    }
}
