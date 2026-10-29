<?php

namespace Database\Factories;

use App\Enums\RegistrationStatus;
use App\Enums\ViolationAction;
use App\Enums\ViolationReasonCode;
use App\Models\JudgeAssignment;
use App\Models\Registration;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<Violation> */
class ViolationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory()->state(['status' => RegistrationStatus::Verified, 'verified_at' => now()]),
            'judge_id' => User::factory()->judge(),
            'action' => ViolationAction::Warning,
            'reason_code' => ViolationReasonCode::Shouting,
            'reason' => 'Berteriak',
            'note' => null,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(fn (Violation $violation) => JudgeAssignment::query()->firstOrCreate([
            'competition_class_id' => $violation->registration()->value('competition_class_id'),
            'judge_id' => $violation->judge_id,
        ]));
    }
}
