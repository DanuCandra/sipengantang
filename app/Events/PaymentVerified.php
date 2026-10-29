<?php

namespace App\Events;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class PaymentVerified implements ShouldDispatchAfterCommit
{
    use Dispatchable;

    public function __construct(public readonly int $paymentSubmissionId, public readonly int $registrationId) {}
}
