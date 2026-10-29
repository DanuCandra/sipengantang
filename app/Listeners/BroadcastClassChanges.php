<?php

namespace App\Listeners;

use App\Events\ClassStateChanged;
use App\Events\JudgeScoreSubmitted;
use App\Events\JudgingStateChanged;
use App\Events\ParticipantDisqualified;
use App\Events\RankingUpdated;
use App\Events\ResultFinalized;
use App\Events\WarningCreated;

class BroadcastClassChanges
{
    public function handle(JudgeScoreSubmitted|ParticipantDisqualified|WarningCreated|ResultFinalized|RankingUpdated|JudgingStateChanged $event): void
    {
        ClassStateChanged::dispatch($event->competitionClassId);
    }
}
