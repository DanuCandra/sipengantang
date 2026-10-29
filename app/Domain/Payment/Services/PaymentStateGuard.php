<?php

namespace App\Domain\Payment\Services;

use App\Enums\CompetitionClassStatus;
use App\Enums\EventStatus;
use App\Models\CompetitionClass;
use Illuminate\Validation\ValidationException;

class PaymentStateGuard
{
    public function ensureMutable(CompetitionClass $competitionClass): void
    {
        $competitionClass->loadMissing('event');

        if (in_array($competitionClass->status, [CompetitionClassStatus::Finalized, CompetitionClassStatus::Cancelled], true)
            || $competitionClass->event->status === EventStatus::Cancelled) {
            throw ValidationException::withMessages(['payment' => 'Pembayaran tidak dapat diubah pada kelas final, kelas yang dibatalkan, atau event yang dibatalkan.']);
        }
    }
}
