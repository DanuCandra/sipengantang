<?php

namespace App\Enums;

enum ViolationReasonCode: string
{
    case Shouting = 'SHOUTING';
    case Provocation = 'PROVOCATION';
    case Disturbing = 'DISTURBING';
    case LateHanging = 'LATE_HANGING';
    case NumberManipulation = 'NUMBER_MANIPULATION';
    case Other = 'OTHER';

    public function label(): string
    {
        return match ($this) {
            self::Shouting => 'Berteriak',
            self::Provocation => 'Memprovokasi peserta lain',
            self::Disturbing => 'Mengganggu jalannya lomba',
            self::LateHanging => 'Terlambat menggantangkan burung',
            self::NumberManipulation => 'Memanipulasi nomor gantangan',
            self::Other => 'Lainnya',
        };
    }
}
