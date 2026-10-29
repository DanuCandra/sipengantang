<?php

namespace Database\Factories;

use App\Models\RankingRun;
use App\Models\RankingTieResolution;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<RankingTieResolution> */
class RankingTieResolutionFactory extends Factory
{
    public function definition(): array
    {
        return ['ranking_run_id' => RankingRun::factory(), 'group_key' => hash('sha256', '[1,2]'), 'ordered_registration_ids' => [2, 1], 'note' => 'Undian di lokasi: peserta kedua memperoleh total dadu tertinggi.', 'created_by' => User::factory()->admin()];
    }
}
