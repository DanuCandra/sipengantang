<?php

namespace App\Enums;

enum UserRole: string
{
    case Owner = 'OWNER';
    case Admin = 'ADMIN';
    case Judge = 'JUDGE';
    case Participant = 'PARTICIPANT';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Owner',
            self::Admin => 'Admin',
            self::Judge => 'Juri',
            self::Participant => 'Peserta',
        };
    }

    public function dashboardRoute(): string
    {
        return match ($this) {
            self::Owner => 'owner.dashboard',
            self::Admin => 'admin.dashboard',
            self::Judge => 'judge.dashboard',
            self::Participant => 'participant.dashboard',
        };
    }
}
