<?php

namespace App\Events;

use App\Enums\EventStatus;
use App\Models\CompetitionClass;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldRescue;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;

class ClassStateChanged implements ShouldBroadcast, ShouldDispatchAfterCommit, ShouldRescue
{
    use Dispatchable;

    // Even with QUEUE_CONNECTION=sync, an unavailable WebSocket never blocks a write.
    public string $connection = 'database';

    public int $tries = 3;

    public array $backoff = [5, 15];

    public function __construct(public readonly int $competitionClassId) {}

    public function broadcastAs(): string
    {
        return 'class.updated';
    }

    public function broadcastWith(): array
    {
        return ['class_id' => $this->competitionClassId];
    }

    public function broadcastOn(): array
    {
        $class = CompetitionClass::query()->with('event')->find($this->competitionClassId);
        if (! $class) {
            return [];
        }
        $channels = [new PrivateChannel('event.'.$class->event_id.'.admin')];
        if ($class->event->published_at && in_array($class->event->status, [EventStatus::Upcoming, EventStatus::Ongoing, EventStatus::Completed], true)) {
            $channels[] = new Channel('class.'.$class->id.'.public');
        }
        foreach ($class->judgeAssignments()->active()->pluck('judge_id') as $id) {
            $channels[] = new PrivateChannel('class.'.$class->id.'.judge.'.$id);
        }

        return $channels;
    }
}
