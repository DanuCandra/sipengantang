<?php

namespace App\Http\Controllers\Participant;

use App\Enums\EventStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(): View
    {
        $events = Event::query()
            ->whereNotNull('published_at')
            ->whereIn('status', [EventStatus::Upcoming, EventStatus::Ongoing])
            ->withCount('competitionClasses')
            ->orderBy('start_at')
            ->orderBy('id')
            ->paginate(12);

        return view('participant.events.index', compact('events'));
    }
}
