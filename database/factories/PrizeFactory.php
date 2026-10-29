<?php

namespace Database\Factories;

use App\Enums\PrizeType;
use App\Models\CompetitionClass;
use App\Models\Prize;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Prize>
 */
class PrizeFactory extends Factory
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
            'rank' => 1,
            'type' => PrizeType::Cash,
            'title' => 'Juara 1',
            'description' => 'Hadiah pemenang peringkat pertama.',
            'cash_amount' => 1000000,
        ];
    }
}
