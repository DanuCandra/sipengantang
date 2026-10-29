<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Judging\Actions\StartClassJudgingAction;
use App\Http\Controllers\Controller;
use App\Models\CompetitionClass;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class StartedClassJudgingController extends Controller
{
    public function __invoke(Request $request, Event $event, CompetitionClass $competitionClass, StartClassJudgingAction $action): RedirectResponse
    {
        abort_unless($competitionClass->event_id === $event->id, 404);
        Gate::authorize('manageJudging', $competitionClass);
        $action->execute($request->user(), $competitionClass, $request->ip());

        return back()->with('success', 'Penjurian kelas dimulai. Juri yang ditugaskan dapat mengisi nilai.');
    }
}
