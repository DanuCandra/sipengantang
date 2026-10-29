<?php

namespace Database\Factories;

use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Event>
 */
class EventFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true).' Cup';

        return [
            'owner_id' => User::factory()->owner(),
            'name' => str($name)->title(),
            'slug' => str($name)->slug().'-'.fake()->unique()->numerify('###'),
            'description' => fake()->paragraph(),
            'location_name' => fake()->company().' Arena',
            'address' => fake()->address(),
            'registration_open_at' => now()->addDay(),
            'registration_close_at' => now()->addDays(10),
            'start_at' => now()->addDays(14),
            'end_at' => now()->addDays(14)->addHours(8),
            'contact_person' => fake()->name(),
            'contact_phone' => '08'.fake()->numerify('##########'),
            'status' => EventStatus::Draft,
        ];
    }

    public function publishedUpcoming(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => EventStatus::Upcoming,
            'published_at' => now(),
            'registration_open_at' => now()->subDay(),
            'registration_close_at' => now()->addDays(10),
        ]);
    }
}
