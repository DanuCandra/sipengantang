<?php

namespace App\Domain\Registration\Services;

use App\Enums\RegistrationStatus;
use App\Models\CompetitionClass;

class RegistrationQuotaService
{
    /**
     * Pending and rejected registrations conservatively reserve a slot until the
     * product defines an expiry/release policy. Only explicit cancellation frees it.
     */
    public function usedSlots(CompetitionClass $competitionClass): int
    {
        return $competitionClass->registrations()
            ->where('status', '!=', RegistrationStatus::Cancelled)
            ->count();
    }

    public function hasAvailableSlot(CompetitionClass $competitionClass): bool
    {
        return $competitionClass->quota === null || $this->usedSlots($competitionClass) < $competitionClass->quota;
    }
}
