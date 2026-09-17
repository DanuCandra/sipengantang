<?php

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        Gate::define(
            'access-owner-dashboard',
            fn (User $user): bool => $user->hasRole(UserRole::Owner),
        );
        Gate::define(
            'access-admin-dashboard',
            fn (User $user): bool => $user->hasRole(UserRole::Admin),
        );
        Gate::define(
            'access-judge-dashboard',
            fn (User $user): bool => $user->hasRole(UserRole::Judge),
        );
        Gate::define(
            'access-participant-dashboard',
            fn (User $user): bool => $user->hasRole(UserRole::Participant),
        );
    }
}
