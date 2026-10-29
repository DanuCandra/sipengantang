<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Judging\Actions\ReplaceAbsentJudgeAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ReplaceAbsentJudgeRequest;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\JudgeAssignment;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class AbsentJudgeReplacementController extends Controller
{
    public function __invoke(ReplaceAbsentJudgeRequest $request, Event $event, CompetitionClass $competitionClass, JudgeAssignment $judgeAssignment, ReplaceAbsentJudgeAction $action): RedirectResponse
    {
        $replacement = User::query()->where('email', $request->validated('replacement_email'))->firstOrFail();
        $action->execute($request->user(), $competitionClass, $judgeAssignment, $replacement, $request->validated('replacement_note'), $request->ip());

        return back()->with('success', 'Juri absen telah diganti. Juri lama tidak lagi memiliki akses penilaian kelas ini.');
    }
}
