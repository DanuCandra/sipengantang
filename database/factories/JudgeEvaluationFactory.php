<?php

namespace Database\Factories;

use App\Enums\EvaluationStatus;
use App\Enums\RegistrationStatus;
use App\Models\JudgeAssignment;
use App\Models\JudgeEvaluation;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JudgeEvaluation> */
class JudgeEvaluationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory()->state(['status' => RegistrationStatus::Verified, 'verified_at' => now()]),
            'judge_id' => User::factory()->judge(),
            'status' => EvaluationStatus::Draft,
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (JudgeEvaluation $evaluation): void {
            JudgeAssignment::query()->firstOrCreate([
                'competition_class_id' => $evaluation->registration()->value('competition_class_id'),
                'judge_id' => $evaluation->judge_id,
            ]);
        });
    }

    public function submitted(): static
    {
        return $this->state(fn () => ['status' => EvaluationStatus::Submitted, 'submitted_at' => now()]);
    }
}
