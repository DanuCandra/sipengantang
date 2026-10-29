<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldRescue;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class CertificateReady implements ShouldBroadcast, ShouldDispatchAfterCommit, ShouldRescue
{
    use Dispatchable;

    public string $connection = 'database';

    public int $tries = 3;

    public function __construct(private readonly int $participantId, private readonly int $certificateId) {}

    public function broadcastAs(): string
    {
        return 'certificate.ready';
    }

    public function broadcastWith(): array
    {
        return ['certificate_id' => $this->certificateId];
    }

    public function broadcastOn(): array
    {
        return [new PrivateChannel('user.'.$this->participantId)];
    }
}
