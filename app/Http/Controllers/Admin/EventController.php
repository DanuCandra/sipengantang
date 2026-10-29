<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Event\Actions\UpdateEventAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\UpdateEventRequest;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        $events = $request->user()->administeredEvents()->with('owner:id,name')->withCount('competitionClasses')->latest('start_at')->paginate(10);

        return view('admin.events.index', compact('events'));
    }

    public function show(Event $event): View
    {
        Gate::authorize('view', $event);
        $event->load(['owner:id,name', 'competitionClasses' => fn ($query) => $query->withCount('prizes')->orderBy('start_at')]);

        return view('admin.events.show', compact('event'));
    }

    public function edit(Event $event): View
    {
        Gate::authorize('update', $event);

        return view('admin.events.edit', compact('event'));
    }

    public function update(UpdateEventRequest $request, Event $event, UpdateEventAction $action): RedirectResponse
    {
        Gate::authorize('update', $event);
        $action->execute($request->user(), $event, $request->safe()->except('banner'), $request->file('banner'), $request->ip());

        return redirect()->route('admin.events.show', $event)->with('success', 'Informasi event berhasil diperbarui.');
    }
}
