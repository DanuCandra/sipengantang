<?php

namespace App\Http\Controllers\Judge;

use App\Domain\Judging\Actions\SaveEvaluationAction;
use App\Domain\Judging\Services\JudgingStateGuard;
use App\Domain\Judging\Services\ViolationRuleService;
use App\Enums\EvaluationStatus;
use App\Enums\RegistrationStatus;
use App\Enums\ViolationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Judge\SaveEvaluationRequest;
use App\Models\CompetitionClass;
use App\Models\JudgeEvaluation;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class EvaluationController extends Controller
{
    public function edit(Request $request, CompetitionClass $competitionClass, Registration $registration, JudgingStateGuard $stateGuard, ViolationRuleService $violationRules): View
    {
        abort_unless($registration->competition_class_id === $competitionClass->id, 404);
        Gate::authorize('score', [JudgeEvaluation::class, $registration]);
        abort_unless(in_array($registration->status, [RegistrationStatus::Verified, RegistrationStatus::Disqualified], true), 403);
        $competitionClass->load(['criteria', 'event']);
        $registration->load(['bird', 'participant:id,name']);
        $evaluation = $registration->judgeEvaluations()->where('judge_id', $request->user()->id)->with('scores')->first();
        $scores = $evaluation?->scores->pluck('score', 'criterion_id')->all() ?? [];
        $canWrite = $registration->status === RegistrationStatus::Verified && $evaluation?->status !== EvaluationStatus::Submitted && $stateGuard->isScoringOpen($competitionClass);
        $canViolate = $registration->status === RegistrationStatus::Verified && $stateGuard->isScoringOpen($competitionClass);
        $registration->loadCount(['violations as warning_count' => fn ($query) => $query->where('action', ViolationAction::Warning)]);
        $violations = $registration->violations()->with('judge:id,name')->latest('id')->paginate(10, ['*'], 'violations_page');
        $warningReasons = $violationRules->reasonsFor(ViolationAction::Warning);
        $disqualificationReasons = $violationRules->reasonsFor(ViolationAction::Disqualification);

        return view('judge.evaluations.edit', compact('competitionClass', 'registration', 'evaluation', 'scores', 'canWrite', 'canViolate', 'violations', 'warningReasons', 'disqualificationReasons'));
    }

    public function draft(SaveEvaluationRequest $request, CompetitionClass $competitionClass, Registration $registration, SaveEvaluationAction $action): RedirectResponse
    {
        $action->execute($request->user(), $registration, $request->validated('scores', []), EvaluationStatus::Draft, $request->ip());

        return redirect()->route('judge.evaluations.edit', [$competitionClass, $registration])->with('success', 'Draft penilaian tersimpan. Nilai belum masuk perhitungan.');
    }

    public function submit(SaveEvaluationRequest $request, CompetitionClass $competitionClass, Registration $registration, SaveEvaluationAction $action): RedirectResponse
    {
        $action->execute($request->user(), $registration, $request->validated('scores', []), EvaluationStatus::Submitted, $request->ip());

        return redirect()->route('judge.evaluations.edit', [$competitionClass, $registration])->with('success', 'Nilai berhasil dikirim dan sekarang hanya dapat dibaca.');
    }
}
