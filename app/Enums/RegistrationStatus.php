<?php

namespace App\Enums;

enum RegistrationStatus: string
{
    case PendingPayment = 'PENDING_PAYMENT';
    case WaitingVerification = 'WAITING_VERIFICATION';
    case Verified = 'VERIFIED';
    case PaymentRejected = 'PAYMENT_REJECTED';
    case Cancelled = 'CANCELLED';
    case Disqualified = 'DISQUALIFIED';

    public function label(): string
    {
        return match ($this) {
            self::PendingPayment => 'Menunggu pembayaran',
            self::WaitingVerification => 'Menunggu verifikasi',
            self::Verified => 'Terverifikasi',
            self::PaymentRejected => 'Pembayaran ditolak',
            self::Cancelled => 'Dibatalkan',
            self::Disqualified => 'Diskualifikasi',
        };
    }
}
