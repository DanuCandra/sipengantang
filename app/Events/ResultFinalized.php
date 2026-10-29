<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class ResultFinalized implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $rankingRunId, public readonly int $competitionClassId) {}
}
