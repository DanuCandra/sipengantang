<?php

namespace App\Http\Controllers;

use App\Enums\EventStatus;
use App\Models\Event;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $event = Event::query()
            ->whereNotNull('published_at')
            ->whereIn('status', [EventStatus::Upcoming, EventStatus::Ongoing])
            ->withCount('competitionClasses')
            ->orderBy('start_at')
            ->orderBy('id')
            ->first();

        return view('welcome', [
            'featuredEvent' => $event ? [
                'name' => $event->name,
                'date' => $event->start_at->translatedFormat('d F Y'),
                'location' => $event->location_name,
                'classes' => $event->competition_classes_count,
                'url' => route('events.show', $event),
                'banner_url' => $event->banner_path ? asset('storage/'.$event->banner_path) : null,
            ] : null,
        ]);
    }
}
