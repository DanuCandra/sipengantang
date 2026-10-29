<?php

namespace App\Http\Controllers\Owner;

use App\Domain\Event\Actions\CreateEventAction;
use App\Domain\Event\Actions\UpdateEventAction;
use App\Domain\Judging\Queries\EventViolationQuery;
use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreEventRequest;
use App\Http\Requests\Owner\UpdateEventRequest;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EventController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Event::class);
        $events = Event::query()->where('owner_id', $request->user()->id)->withCount(['admins', 'competitionClasses'])->latest('start_at')->paginate(10);

        return view('owner.events.index', compact('events'));
    }

    public function create(): View
    {
        Gate::authorize('create', Event::class);

        return view('owner.events.create');
    }

    public function store(StoreEventRequest $request, CreateEventAction $action): RedirectResponse
    {
        Gate::authorize('create', Event::class);
        $event = $action->execute($request->user(), $request->safe()->except('banner'), $request->file('banner'), $request->ip());

        return redirect()->route('owner.events.show', $event)->with('success', 'Event berhasil dibuat sebagai draft.');
    }

    public function show(Request $request, Event $event, EventViolationQuery $violationQuery): View
    {
        Gate::authorize('assignAdmin', $event);
        $event->load(['admins:id,name,email,status', 'competitionClasses' => fn ($query) => $query->withCount('prizes')->orderBy('start_at')]);
        $violations = $violationQuery->forEvent($event)->paginate(10, ['*'], 'violations_page');
        $availableAdmins = User::query()->where('status', UserStatus::Active)
            ->whereHas('roles', fn ($query) => $query->where('role', UserRole::Admin))
            ->whereDoesntHave('eventAssignments', fn ($query) => $query->where('event_id', $event->id))
            ->orderBy('name')->orderBy('id')->paginate(25, ['id', 'name', 'email'], 'admins_page')->withQueryString();

        return view('owner.events.show', compact('event', 'violations', 'availableAdmins'));
    }

    public function edit(Event $event): View
    {
        Gate::authorize('update', $event);

        return view('owner.events.edit', compact('event'));
    }

    public function update(UpdateEventRequest $request, Event $event, UpdateEventAction $action): RedirectResponse
    {
        Gate::authorize('update', $event);
        $action->execute($request->user(), $event, $request->safe()->except('banner'), $request->file('banner'), $request->ip());

        return redirect()->route('owner.events.show', $event)->with('success', 'Informasi event berhasil diperbarui.');
    }
}
