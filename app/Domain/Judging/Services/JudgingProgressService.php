<?php

namespace App\Domain\Judging\Services;

use App\Enums\EvaluationStatus;
use App\Enums\RegistrationStatus;
use App\Models\CompetitionClass;
use App\Models\JudgeEvaluation;

class JudgingProgressService
{
    /** @return array{eligible: int, assigned: int, expected: int, submitted: int, drafts: int, percent: int, by_judge: array<int, array<string, int>>} */
    public function summary(CompetitionClass $competitionClass): array
    {
        $eligible = $competitionClass->registrations()->where('status', RegistrationStatus::Verified)->count();
        $judgeIds = $competitionClass->judgeAssignments()->active()->pluck('judge_id');
        $assigned = $judgeIds->count();
        $groups = JudgeEvaluation::query()
            ->whereIn('judge_id', $judgeIds)
            ->whereHas('registration', fn ($query) => $query->where('competition_class_id', $competitionClass->id)->where('status', RegistrationStatus::Verified))
            ->select('judge_id', 'status')->selectRaw('COUNT(*) as total')
            ->groupBy('judge_id', 'status')->get();
        $byJudge = [];
        $submitted = 0;
        $drafts = 0;
        foreach ($groups as $group) {
            $byJudge[$group->judge_id][$group->status->value] = (int) $group->total;
            if ($group->status === EvaluationStatus::Submitted) {
                $submitted += (int) $group->total;
            } else {
                $drafts += (int) $group->total;
            }
        }
        $expected = $eligible * $assigned;

        return ['eligible' => $eligible, 'assigned' => $assigned, 'expected' => $expected, 'submitted' => $submitted, 'drafts' => $drafts, 'percent' => $expected === 0 ? 0 : (int) round($submitted / $expected * 100), 'by_judge' => $byJudge];
    }
}
