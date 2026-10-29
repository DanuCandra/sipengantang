<?php

namespace App\Domain\Registration\Services;

use App\Models\Registration;

class RegistrationNumberGenerator
{
    public function generate(Registration $registration, int $eventYear): string
    {
        return sprintf('REG-%d-%06d', $eventYear, $registration->id);
    }
}
