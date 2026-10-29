<?php

namespace Database\Factories;

use App\Models\CompetitionClass;
use App\Models\JudgeAssignment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<JudgeAssignment> */
class JudgeAssignmentFactory extends Factory
{
    public function definition(): array
    {
        return ['competition_class_id' => CompetitionClass::factory(), 'judge_id' => User::factory()->judge()];
    }
}
