<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class ParticipantDisqualified implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $violationId, public readonly int $registrationId, public readonly int $competitionClassId) {}
}
