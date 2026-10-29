<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Models\Event;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\View\View;

class PublicEventController extends Controller
{
    public function index(): View
    {
        /** @var LengthAwarePaginator<Event> $events */
        $events = Event::query()
            ->whereNotNull('published_at')
            ->whereIn('status', [EventStatus::Upcoming, EventStatus::Ongoing, EventStatus::Completed])
            ->withCount('competitionClasses')
            ->orderBy('start_at')
            ->orderBy('id')
            ->paginate(12);

        return view('events.index', compact('events'));
    }

    public function show(Event $event): View
    {
        abort_if($event->published_at === null || in_array($event->status, [EventStatus::Draft, EventStatus::Cancelled], true), 404);

        $event->load([
            'competitionClasses' => fn ($query) => $query
                ->with(['prizes'])
                ->withCount(['registrations as occupied_slots' => fn ($registrations) => $registrations->where('status', '!=', RegistrationStatus::Cancelled)])
                ->orderBy('start_at')
                ->orderBy('id'),
        ]);

        return view('events.show', compact('event'));
    }
}
