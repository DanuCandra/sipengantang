<?php

namespace App\Enums;

enum EvaluationStatus: string
{
    case Draft = 'DRAFT';
    case Submitted = 'SUBMITTED';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Submitted => 'Sudah dikirim',
        };
    }
}
