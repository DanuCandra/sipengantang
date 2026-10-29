<?php

namespace App\Domain\Event\Queries;

use App\Models\Event;
use Illuminate\Http\Request;

class DashboardEventContext
{
    public function resolve(Request $request): array
    {
        if ($request->attributes->has('admin_event_context')) {
            return $request->attributes->get('admin_event_context');
        }
        $query = Event::query()->whereHas('adminAssignments', fn ($query) => $query->where('user_id', $request->user()->id));
        $events = (clone $query)->latest('start_at')->latest('id')->limit(50)->get(['id', 'name']);
        $routeEvent = $request->route('event');
        $explicit = $request->query->has('event_id');
        $selectedId = $routeEvent instanceof Event ? $routeEvent->id : ($explicit ? $request->integer('event_id') : $request->session()->get('dashboard.admin.event_id'));
        $selected = $selectedId ? (clone $query)->find($selectedId, ['id', 'name']) : null;
        abort_if($explicit && ! $selected, 403);
        $selected ??= $events->first();
        if ($selected) {
            $request->session()->put('dashboard.admin.event_id', $selected->id);
            if (! $events->contains('id', $selected->id)) {
                $events->prepend($selected);
            }
        }
        $context = compact('events', 'selected');
        $request->attributes->set('admin_event_context', $context);

        return $context;
    }
}
