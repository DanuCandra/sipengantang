<?php

namespace App\Http\Controllers\Owner;

use App\Domain\Event\Actions\AssignEventAdminAction;
use App\Domain\Event\Actions\CreateAndAssignEventAdminAction;
use App\Domain\Event\Actions\RemoveEventAdminAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\AssignEventAdminRequest;
use App\Http\Requests\Owner\StoreEventAdminAccountRequest;
use App\Models\Event;
use App\Models\EventAdmin;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class EventAdminController extends Controller
{
    public function store(AssignEventAdminRequest $request, Event $event, AssignEventAdminAction $action): RedirectResponse
    {
        Gate::authorize('assignAdmin', $event);
        $admin = User::query()->where('email', $request->validated('email'))->firstOrFail();
        $action->execute($request->user(), $event, $admin, $request->ip());

        return back()->with('success', 'Admin berhasil ditugaskan ke event.');
    }

    public function destroy(Request $request, Event $event, EventAdmin $eventAdmin, RemoveEventAdminAction $action): RedirectResponse
    {
        Gate::authorize('assignAdmin', $event);
        abort_unless($eventAdmin->event_id === $event->id, 404);
        $action->execute($request->user(), $event, $eventAdmin, $request->ip());

        return back()->with('success', 'Penugasan admin berhasil dihapus.');
    }

    public function storeAccount(StoreEventAdminAccountRequest $request, Event $event, CreateAndAssignEventAdminAction $action): RedirectResponse
    {
        Gate::authorize('assignAdmin', $event);
        $action->execute($request->user(), $event, $request->validated(), $request->ip());

        return back()->with('success', 'Akun Admin baru berhasil dibuat dan ditugaskan.');
    }
}
