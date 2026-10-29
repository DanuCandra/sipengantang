<?php

namespace Database\Factories;

use App\Enums\CriterionType;
use App\Models\CompetitionClass;
use App\Models\CompetitionClassCriterion;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompetitionClassCriterion>
 */
class CompetitionClassCriterionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'competition_class_id' => CompetitionClass::factory(),
            'code' => 'C1',
            'name' => 'Irama Lagu',
            'weight' => '0.400000',
            'type' => CriterionType::Benefit,
            'min_score' => 1,
            'max_score' => 10,
            'sort_order' => 1,
        ];
    }
}
