<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Judging\Actions\AssignJudgeAction;
use App\Domain\Judging\Actions\CreateAndAssignJudgeAction;
use App\Domain\Judging\Services\JudgingProgressService;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AssignJudgeRequest;
use App\Http\Requests\Admin\StoreJudgeAccountRequest;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\JudgeEvaluation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class JudgeAssignmentController extends Controller
{
    public function create(Event $event, CompetitionClass $competitionClass): View
    {
        abort_unless($competitionClass->event_id === $event->id, 404);
        Gate::authorize('manageJudging', $competitionClass);

        return view('admin.judges.create', compact('event', 'competitionClass'));
    }

    public function index(Event $event, CompetitionClass $competitionClass, JudgingProgressService $progressService): View
    {
        abort_unless($competitionClass->event_id === $event->id, 404);
        Gate::authorize('manageJudging', $competitionClass);
        $competitionClass->load('event');
        $assignments = $competitionClass->judgeAssignments()->with(['judge:id,name,email,status', 'replacement.judge:id,name'])->orderBy('id')->paginate(15);
        $submittedJudgeIds = JudgeEvaluation::query()->submitted()->whereHas('registration', fn ($query) => $query->where('competition_class_id', $competitionClass->id))->distinct()->pluck('judge_id')->all();
        $progress = $progressService->summary($competitionClass);

        return view('admin.judges.index', compact('event', 'competitionClass', 'assignments', 'progress', 'submittedJudgeIds'));
    }

    public function store(AssignJudgeRequest $request, Event $event, CompetitionClass $competitionClass, AssignJudgeAction $action): RedirectResponse
    {
        $judge = User::query()->where('email', $request->validated('email'))->firstOrFail();
        $action->execute($request->user(), $competitionClass, $judge, $request->ip());

        return back()->with('success', 'Juri berhasil ditugaskan ke kelas.');
    }

    public function storeAccount(StoreJudgeAccountRequest $request, Event $event, CompetitionClass $competitionClass, CreateAndAssignJudgeAction $action): RedirectResponse
    {
        $action->execute($request->user(), $competitionClass, $request->validated(), $request->ip());

        return redirect()->route('admin.events.classes.judges.index', [$event, $competitionClass])->with('success', 'Akun Juri dibuat dan ditugaskan ke kelas.');
    }
}
