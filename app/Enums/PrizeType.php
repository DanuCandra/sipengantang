<?php

namespace App\Enums;

enum PrizeType: string
{
    case Cash = 'CASH';
    case Item = 'ITEM';
    case Other = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::Cash => 'Uang',
            self::Item => 'Barang',
            self::Other => 'Lainnya',
        };
    }
}
