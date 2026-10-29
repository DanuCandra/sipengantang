<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Judging\Services\JudgingProgressService;
use App\Domain\Ranking\Actions\FinalizeClassResultAction;
use App\Domain\Ranking\Actions\RecalculateProvisionalRankingAction;
use App\Domain\Ranking\Actions\RecordDrawResultAction;
use App\Domain\Ranking\Services\RankingService;
use App\Enums\RankingRunType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\FinalizeClassResultRequest;
use App\Http\Requests\Admin\RankingOperationRequest;
use App\Http\Requests\Admin\RecordDrawResultRequest;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\RankingRun;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RankingController extends Controller
{
    public function show(Request $request, Event $event, CompetitionClass $competitionClass, JudgingProgressService $progressService, RankingService $ranking): View|JsonResponse
    {
        abort_unless($competitionClass->event_id === $event->id, 404);
        Gate::authorize('manageJudging', $competitionClass);
        $run = $competitionClass->rankingRuns()->where('type', RankingRunType::Final)->latest('id')->first()
            ?? $competitionClass->rankingRuns()->where('type', RankingRunType::Provisional)->latest('id')->first();
        $results = $run?->results()->orderBy('rank')->orderBy('registration_id')->paginate(15);
        $progress = $progressService->summary($competitionClass);
        $groups = $run ? $ranking->tieGroups($run->calculation_snapshot) : [];
        $resolutions = $run?->tieResolutions()->with('creator:id,name')->get()->keyBy('group_key') ?? collect();
        $entries = $run ? collect($run->input_snapshot['registrations'])->keyBy('registration_id')->all() : [];
        if ($request->boolean('fragment')) {
            return response()->json(['run_id' => $run?->id, 'html' => view('admin.rankings.partials.results', compact('results', 'run'))->render()])->header('Cache-Control', 'private, no-store');
        }

        return view('admin.rankings.show', compact('event', 'competitionClass', 'run', 'results', 'progress', 'groups', 'resolutions', 'entries'));
    }

    public function recalculate(RankingOperationRequest $request, Event $event, CompetitionClass $competitionClass, RecalculateProvisionalRankingAction $action): RedirectResponse
    {
        $run = $action->execute($competitionClass);

        return back()->with('success', $run ? 'Ranking sementara dihitung dari nilai submitted terbaru.' : 'Penjurian tidak terbuka; tidak ada perubahan hasil.');
    }

    public function finalize(FinalizeClassResultRequest $request, Event $event, CompetitionClass $competitionClass, FinalizeClassResultAction $action): RedirectResponse
    {
        $action->execute($request->user(), $competitionClass, $request->ip());

        return back()->with('success', 'Hasil final tersimpan dan dipublikasikan. Kelas terkunci.');
    }

    public function draw(RecordDrawResultRequest $request, Event $event, CompetitionClass $competitionClass, RankingRun $rankingRun, RecordDrawResultAction $action): RedirectResponse
    {
        $action->execute($request->user(), $competitionClass, $rankingRun, $request->validated('group_key'), $request->validated('ordered_registration_ids'), $request->validated('note'), $request->ip());

        return back()->with('success', 'Urutan hasil lempar dadu tercatat. Nilai TOPSIS asli tidak diubah.');
    }
}
