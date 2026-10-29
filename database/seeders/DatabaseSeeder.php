<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        if (app()->isProduction()) {
            return;
        }

        $demoUsers = [
            ['name' => 'Owner GantangScore', 'email' => 'owner@gmail.com', 'role' => UserRole::Owner],
            ['name' => 'Albab', 'email' => 'albab@gmail.com', 'role' => UserRole::Admin],
            ['name' => 'Juri Demo', 'email' => 'juri@gmail.com', 'role' => UserRole::Judge],
            ['name' => 'Peserta Demo', 'email' => 'peserta@gmail.com', 'role' => UserRole::Participant],
        ];

        foreach ($demoUsers as $demoUser) {
            $user = User::query()->updateOrCreate(
                ['email' => $demoUser['email']],
                [
                    'name' => $demoUser['name'],
                    'status' => UserStatus::Active,
                    'password' => config('gantangscore.demo_password'),
                    'email_verified_at' => now(),
                ],
            );

            $user->roles()->firstOrCreate(['role' => $demoUser['role']]);

            if ($demoUser['role'] === UserRole::Participant) {
                $user->participantProfile()->firstOrCreate();
            }
        }
    }
}
