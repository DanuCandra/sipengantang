<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case WaitingVerification = 'WAITING_VERIFICATION';
    case Verified = 'VERIFIED';
    case Rejected = 'REJECTED';

    public function label(): string
    {
        return match ($this) {
            self::WaitingVerification => 'Menunggu verifikasi',
            self::Verified => 'Terverifikasi',
            self::Rejected => 'Ditolak',
        };
    }
}
