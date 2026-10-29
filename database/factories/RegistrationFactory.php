<?php

namespace Database\Factories;

use App\Enums\RegistrationStatus;
use App\Models\Bird;
use App\Models\CompetitionClass;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
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
            'participant_id' => User::factory()->participant(),
            'bird_id' => fn (array $attributes) => Bird::factory()->create(['owner_id' => $attributes['participant_id']])->id,
            'registration_number' => 'REG-'.now()->year.'-'.Str::upper(Str::random(8)),
            'gantangan_number' => null,
            'registration_fee_snapshot' => 150000,
            'status' => RegistrationStatus::PendingPayment,
            'registered_at' => now(),
        ];
    }
}
