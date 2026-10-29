<?php

namespace Database\Factories;

use App\Models\Bird;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bird>
 */
class BirdFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'owner_id' => User::factory()->participant(),
            'name' => fake()->firstName(),
            'bird_type' => fake()->randomElement(['Murai Batu', 'Kacer', 'Cucak Hijau', 'Kenari']),
            'photo_path' => null,
            'description' => fake()->optional()->sentence(),
        ];
    }
}
