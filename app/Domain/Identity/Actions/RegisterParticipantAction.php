<?php

namespace App\Domain\Identity\Actions;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Support\Facades\DB;

final class RegisterParticipantAction
{
    /**
     * @param  array{name: string, email: string, phone?: string|null, password: string}  $attributes
     */
    public function execute(array $attributes): User
    {
        return DB::transaction(function () use ($attributes): User {
            $user = User::query()->create([
                'name' => $attributes['name'],
                'email' => $attributes['email'],
                'phone' => $attributes['phone'] ?? null,
                'status' => UserStatus::Active,
                'password' => $attributes['password'],
            ]);

            $user->roles()->create(['role' => UserRole::Participant]);
            $user->participantProfile()->create();

            return $user;
        });
    }
}
