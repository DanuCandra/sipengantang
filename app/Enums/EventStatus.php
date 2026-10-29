<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'DRAFT';
    case Upcoming = 'UPCOMING';
    case Ongoing = 'ONGOING';
    case Completed = 'COMPLETED';
    case Cancelled = 'CANCELLED';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Upcoming => 'Akan datang',
            self::Ongoing => 'Berlangsung',
            self::Completed => 'Selesai',
            self::Cancelled => 'Dibatalkan',
        };
    }
}
