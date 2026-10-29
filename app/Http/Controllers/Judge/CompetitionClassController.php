<?php

namespace App\Http\Controllers\Judge;

use App\Domain\Judging\Queries\JudgeClassQuery;
use App\Enums\CompetitionClassStatus;
use App\Enums\RegistrationStatus;
use App\Enums\ViolationAction;
use App\Http\Controllers\Controller;
use App\Models\CompetitionClass;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CompetitionClassController extends Controller
{
    public function index(Request $request, JudgeClassQuery $query): View
    {
        $data = $request->validate(['view' => ['sometimes', 'in:assignments,scoring,history']]);
        $mode = $data['view'] ?? 'assignments';
        $classes = $query->forJudge($request->user())
            ->when($mode === 'scoring', fn ($query) => $query->where('status', CompetitionClassStatus::Ongoing))
            ->when($mode === 'history', fn ($query) => $query->whereHas('registrations.judgeEvaluations', fn ($evaluations) => $evaluations->submitted()->where('judge_id', $request->user()->id)))
            ->paginate(12)->withQueryString();

        return view('judge.classes.index', compact('classes', 'mode'));
    }

    public function show(Request $request, CompetitionClass $competitionClass): View
    {
        Gate::authorize('judge', $competitionClass);
        $competitionClass->load('event');
        $validated = $request->validate(['search' => ['nullable', 'string', 'max:100']]);
        $search = trim($validated['search'] ?? '');
        $registrations = $competitionClass->registrations()
            ->withCount(['violations as warning_count' => fn ($query) => $query->where('action', ViolationAction::Warning)])
            ->whereIn('status', [RegistrationStatus::Verified, RegistrationStatus::Disqualified])
            ->with(['bird:id,name,bird_type,photo_path', 'participant:id,name',
                'judgeEvaluations' => fn ($query) => $query->where('judge_id', $request->user()->id)->select('id', 'registration_id', 'judge_id', 'status', 'submitted_at'),
            ])
            ->when($search !== '', fn ($query) => $query->where(fn ($nested) => $nested
                ->where('gantangan_number', 'like', '%'.$search.'%')->orWhere('registration_number', 'like', '%'.$search.'%')
                ->orWhereHas('bird', fn ($bird) => $bird->where('name', 'like', '%'.$search.'%'))))
            ->orderBy('gantangan_number')->orderBy('id')->paginate(15)->withQueryString();
        $eligible = $competitionClass->registrations()->where('status', RegistrationStatus::Verified)->count();
        $submitted = $competitionClass->registrations()->where('status', RegistrationStatus::Verified)
            ->whereHas('judgeEvaluations', fn ($query) => $query->where('judge_id', $request->user()->id)->submitted())->count();

        return view('judge.classes.show', compact('competitionClass', 'registrations', 'eligible', 'submitted'));
    }
}
