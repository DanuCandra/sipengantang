<?php

namespace Database\Factories;

use App\Enums\CompetitionClassStatus;
use App\Models\CompetitionClass;
use App\Models\Event;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompetitionClass>
 */
class CompetitionClassFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(['Murai Batu', 'Kacer', 'Cucak Hijau', 'Kenari']).' '.fake()->unique()->numerify('####');

        return [
            'event_id' => Event::factory(),
            'name' => $name,
            'slug' => str($name)->slug(),
            'bird_type' => str($name)->beforeLast(' ')->toString(),
            'description' => fake()->sentence(),
            'registration_fee' => fake()->randomElement([100000, 150000, 200000]),
            'quota' => 36,
            'start_at' => now()->addDays(14),
            'end_at' => now()->addDays(14)->addHours(2),
            'status' => CompetitionClassStatus::Upcoming,
        ];
    }
}
