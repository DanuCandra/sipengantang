<?php

namespace Database\Factories;

use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Models\PaymentSubmission;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/** @extends Factory<PaymentSubmission> */
class PaymentSubmissionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory()->state(['status' => RegistrationStatus::WaitingVerification]),
            'amount' => 150000,
            'proof_path' => 'payments/'.Str::uuid().'.jpg',
            'status' => PaymentStatus::WaitingVerification,
            'uploaded_at' => now(),
        ];
    }
}
