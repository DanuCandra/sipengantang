<?php

namespace App\Listeners;

use App\Events\CertificateGenerated;
use App\Events\CertificateReady;

class BroadcastCertificateReady
{
    public function handle(CertificateGenerated $event): void
    {
        CertificateReady::dispatch($event->participantId, $event->certificateId);
    }
}
