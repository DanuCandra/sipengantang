<?php

namespace App\Domain\Judging\Services;

use App\Enums\CompetitionClassStatus;
use App\Enums\RegistrationStatus;
use App\Models\CompetitionClass;

class LiveClassSnapshot
{
    public function __construct(private JudgingProgressService $progress) {}

    public function publicData(CompetitionClass $class, ?array $summary = null): array
    {
        $class->loadMissing('event');
        $summary ??= $this->progress->summary($class);

        return ['class_id' => $class->id, 'status' => $class->status->value,
            'eligible' => $summary['eligible'], 'submitted' => $summary['submitted'], 'expected' => $summary['expected'], 'percent' => $summary['percent'],
            'disqualified' => $class->registrations()->where('status', RegistrationStatus::Disqualified)->count(),
            'result_url' => $class->status === CompetitionClassStatus::Finalized ? route('events.classes.results', [$class->event, $class]) : null];
    }

    public function adminData(CompetitionClass $class): array
    {
        $summary = $this->progress->summary($class);
        // Include zeros so a DQ cannot leave stale counters in connected monitors.
        $byJudge = $class->judgeAssignments()->active()->pluck('judge_id')->mapWithKeys(fn ($id) => [$id => ['DRAFT' => 0, 'SUBMITTED' => 0]])->all();
        foreach ($summary['by_judge'] as $id => $counts) {
            $byJudge[$id] = [...$byJudge[$id], ...$counts];
        }
        $run = $class->rankingRuns()->latest('id')->first(['id', 'type', 'calculated_at']);

        return [...$this->publicData($class, $summary), 'assigned' => $summary['assigned'], 'drafts' => $summary['drafts'], 'by_judge' => $byJudge,
            'ranking_run_id' => $run?->id, 'ranking_type' => $run?->type->value];
    }

    public function judgeData(CompetitionClass $class, int $judgeId, array $registrationIds): array
    {
        $registrations = $class->registrations()->whereKey($registrationIds)->with(['judgeEvaluations' => fn ($query) => $query->where('judge_id', $judgeId)->select('id', 'registration_id', 'judge_id', 'status')])->get(['id', 'status']);

        return [...$this->publicData($class), 'registrations' => $registrations->map(fn ($registration) => ['id' => $registration->id, 'status' => $registration->status->value, 'evaluation_status' => $registration->judgeEvaluations->first()?->status->value])->all()];
    }
}
