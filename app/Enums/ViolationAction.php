<?php

namespace App\Enums;

enum ViolationAction: string
{
    case Warning = 'WARNING';
    case Disqualification = 'DISQUALIFICATION';

    public function label(): string
    {
        return match ($this) {
            self::Warning => 'Peringatan',
            self::Disqualification => 'Diskualifikasi',
        };
    }
}
