<?php

namespace App\Http\Controllers\Owner;

use App\Domain\Event\Actions\PublishEventAction;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PublishedEventController extends Controller
{
    public function store(Request $request, Event $event, PublishEventAction $action): RedirectResponse
    {
        Gate::authorize('publish', $event);
        $action->execute($request->user(), $event, $request->ip());

        return back()->with('success', 'Event berhasil dipublikasikan.');
    }
}
