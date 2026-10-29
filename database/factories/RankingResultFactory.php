<?php

namespace Database\Factories;

use App\Models\RankingResult;
use App\Models\RankingRun;
use App\Models\Registration;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RankingResult> */
class RankingResultFactory extends Factory
{
    public function definition(): array
    {
        return ['ranking_run_id' => RankingRun::factory(), 'registration_id' => Registration::factory(),
            'entry_snapshot' => ['bird_name' => 'Panglima', 'participant_name' => 'Peserta', 'gantangan_number' => null, 'registration_number' => 'REG-FIXTURE'],
            'criterion_averages' => ['C1' => 8, 'C2' => 8, 'C3' => 8, 'C4' => 8, 'C5' => 8],
            'positive_distance' => 0, 'negative_distance' => 0, 'preference_value' => '0.5000000000', 'rank' => 1, 'is_tied' => false];
    }
}
