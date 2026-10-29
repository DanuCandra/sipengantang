<?php

namespace App\Jobs;

use App\Domain\Ranking\Actions\RecalculateProvisionalRankingAction;
use App\Models\CompetitionClass;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class RecalculateProvisionalRanking implements ShouldBeUniqueUntilProcessing, ShouldQueue
{
    use Queueable;

    public int $uniqueFor = 60;

    public int $tries = 3;

    public int $timeout = 60;

    public function __construct(public int $competitionClassId)
    {
        $this->afterCommit();
    }

    public function uniqueId(): string
    {
        return (string) $this->competitionClassId;
    }

    public function backoff(): array
    {
        return [5, 15];
    }

    public function handle(RecalculateProvisionalRankingAction $action): void
    {
        $class = CompetitionClass::query()->find($this->competitionClassId);
        if ($class !== null) {
            $action->execute($class);
        }
    }
}
