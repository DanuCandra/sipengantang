<?php

namespace App\Domain\Judging\Queries;

use App\Models\Event;
use App\Models\Violation;
use Illuminate\Database\Eloquent\Builder;

class EventViolationQuery
{
    /** @return Builder<Violation> */
    public function forEvent(Event $event): Builder
    {
        return Violation::query()
            ->whereHas('registration.competitionClass', fn ($query) => $query->where('event_id', $event->id))
            ->with(['judge:id,name', 'registration.bird:id,name', 'registration.competitionClass:id,name,event_id'])
            ->latest('id');
    }
}
