<?php

namespace App\Domain\Event\Actions;

use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublishEventAction
{
    public function __construct(private ActivityRecorder $activityRecorder) {}

    public function execute(User $actor, Event $event, ?string $ipAddress = null): Event
    {
        return DB::transaction(function () use ($actor, $event, $ipAddress): Event {
            $locked = Event::query()->lockForUpdate()->findOrFail($event->id);

            if ($locked->status !== EventStatus::Draft) {
                throw ValidationException::withMessages(['event' => 'Hanya event berstatus draft yang dapat dipublikasikan.']);
            }

            $locked->update(['status' => EventStatus::Upcoming, 'published_at' => now()]);
            $this->activityRecorder->record($actor, ActivityAction::EventPublished, $locked, [], $ipAddress);

            return $locked->refresh();
        });
    }
}
