<?php

namespace App\Domain\Registration\Services;

use App\Models\CompetitionClass;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class GantanganNumberService
{
    public const RULES = ['required', 'string', 'max:50', 'regex:/^[1-9][0-9]*$/'];

    public const MESSAGES = [
        'gantangan_number.required' => 'Pilih nomor gantangan yang Anda inginkan.',
        'gantangan_number.string' => 'Nomor gantangan harus berupa angka lebih dari nol.',
        'gantangan_number.regex' => 'Nomor gantangan harus berupa angka lebih dari nol.',
        'gantangan_number.max' => 'Nomor gantangan terlalu panjang.',
    ];

    public static function normalize(mixed $number): mixed
    {
        if (! is_string($number) && ! is_int($number)) {
            return $number;
        }

        return ltrim(trim((string) $number), '0') ?: '0';
    }

    /** Caller must hold the class row lock until the registration is persisted. */
    public function ensureAvailable(CompetitionClass $class, mixed $number): string
    {
        $number = self::normalize($number);
        Validator::make(['gantangan_number' => $number], ['gantangan_number' => self::RULES], self::MESSAGES)->validate();
        // Match legacy zero-padded values too, without rewriting historical numbers.
        if ($class->registrations()->whereNotNull('gantangan_number')->whereRaw("TRIM(LEADING '0' FROM gantangan_number) = ?", [$number])->exists()) {
            throw ValidationException::withMessages(['gantangan_number' => self::conflictMessage()]);
        }

        return $number;
    }

    public static function conflictMessage(): string
    {
        return 'Nomor gantangan sudah digunakan pada kelas ini. Silakan pilih nomor lain.';
    }
}
