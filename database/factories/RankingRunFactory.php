<?php

namespace Database\Factories;

use App\Enums\RankingRunStatus;
use App\Enums\RankingRunType;
use App\Models\CompetitionClass;
use App\Models\RankingRun;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RankingRun> */
class RankingRunFactory extends Factory
{
    public function definition(): array
    {
        return ['competition_class_id' => CompetitionClass::factory(), 'type' => RankingRunType::Provisional, 'status' => RankingRunStatus::Completed,
            'algorithm' => 'TOPSIS', 'algorithm_version' => '1.0.0', 'source_hash' => hash('sha256', 'empty-fixture'),
            'criteria_snapshot' => [], 'input_snapshot' => ['registrations' => [], 'prizes' => [], 'class' => ['name' => 'Kelas fixture', 'event_name' => 'Event fixture']],
            'calculation_snapshot' => ['results' => []], 'calculated_at' => now()];
    }
}
