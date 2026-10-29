<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class CertificateGenerated implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $certificateId, public readonly int $participantId) {}
}
