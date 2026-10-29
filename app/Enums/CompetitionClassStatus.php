<?php

namespace App\Enums;

enum CompetitionClassStatus: string
{
    case Upcoming = 'UPCOMING';
    case Ongoing = 'ONGOING';
    case JudgingCompleted = 'JUDGING_COMPLETED';
    case Finalized = 'FINALIZED';
    case Cancelled = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::Upcoming => 'Akan datang',
            self::Ongoing => 'Berlangsung',
            self::JudgingCompleted => 'Penjurian selesai',
            self::Finalized => 'Final',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
