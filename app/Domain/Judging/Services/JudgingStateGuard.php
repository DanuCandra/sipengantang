<?php

namespace App\Domain\Judging\Services;

use App\Enums\CompetitionClassStatus;
use App\Enums\EventStatus;
use App\Models\CompetitionClass;
use Illuminate\Validation\ValidationException;

class JudgingStateGuard
{
    public function ensureAssignmentsMutable(CompetitionClass $competitionClass): void
    {
        $competitionClass->loadMissing('event');
        if (! in_array($competitionClass->status, [CompetitionClassStatus::Upcoming, CompetitionClassStatus::Ongoing], true)
            || in_array($competitionClass->event->status, [EventStatus::Completed, EventStatus::Cancelled], true)) {
            throw ValidationException::withMessages(['judging' => 'Penugasan Juri pada kelas ini sudah ditutup.']);
        }
    }

    public function isScoringOpen(CompetitionClass $competitionClass): bool
    {
        $competitionClass->loadMissing('event');

        return $competitionClass->status === CompetitionClassStatus::Ongoing
            && $competitionClass->event->status === EventStatus::Ongoing
            && $competitionClass->event->published_at !== null;
    }
}
