<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Event\Actions\CreatePrizeAction;
use App\Domain\Event\Actions\RemovePrizeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StorePrizeRequest;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\Prize;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class PrizeController extends Controller
{
    public function store(StorePrizeRequest $request, Event $event, CompetitionClass $competitionClass, CreatePrizeAction $action): RedirectResponse
    {
        $this->assertNested($event, $competitionClass);
        Gate::authorize('create', [Prize::class, $competitionClass]);
        $action->execute($request->user(), $competitionClass, $request->validated(), $request->ip());

        return back()->with('success', 'Hadiah berhasil ditambahkan.');
    }

    public function destroy(Request $request, Event $event, CompetitionClass $competitionClass, Prize $prize, RemovePrizeAction $action): RedirectResponse
    {
        $this->assertNested($event, $competitionClass);
        abort_unless($prize->competition_class_id === $competitionClass->id, 404);
        Gate::authorize('delete', $prize);
        $action->execute($request->user(), $prize, $request->ip());

        return back()->with('success', 'Hadiah berhasil dihapus.');
    }

    private function assertNested(Event $event, CompetitionClass $competitionClass): void
    {
        abort_unless($competitionClass->event_id === $event->id, 404);
    }
}
