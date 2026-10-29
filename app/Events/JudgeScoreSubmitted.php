<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class JudgeScoreSubmitted implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $evaluationId, public readonly int $registrationId, public readonly int $competitionClassId) {}
}
