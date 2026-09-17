<?php

namespace App\Enums;

enum UserStatus: string
{
    case Active = 'ACTIVE';
    case Inactive = 'INACTIVE';
    case Suspended = 'SUSPENDED';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Aktif',
            self::Inactive => 'Tidak aktif',
            self::Suspended => 'Ditangguhkan',
        };
    }
}
