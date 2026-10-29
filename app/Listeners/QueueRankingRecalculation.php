<?php

namespace App\Listeners;

use App\Events\JudgeScoreSubmitted;
use App\Events\ParticipantDisqualified;
use App\Jobs\RecalculateProvisionalRanking;

class QueueRankingRecalculation
{
    public function handle(JudgeScoreSubmitted|ParticipantDisqualified $event): void
    {
        RecalculateProvisionalRanking::dispatch($event->competitionClassId);
    }
}
