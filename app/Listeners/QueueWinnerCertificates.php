<?php

namespace App\Listeners;

use App\Events\ResultFinalized;
use App\Jobs\GenerateWinnerCertificates;
use Throwable;

class QueueWinnerCertificates
{
    public function handle(ResultFinalized $event): void
    {
        try {
            GenerateWinnerCertificates::dispatch($event->rankingRunId);
        } catch (Throwable $exception) {
            report($exception);
        }
    }
}
