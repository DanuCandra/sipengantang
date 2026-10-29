<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\EventAdmin;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EventAdmin>
 */
class EventAdminFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ['event_id' => Event::factory(), 'user_id' => User::factory()->admin()];
    }
}
