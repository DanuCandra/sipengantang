<?php

use App\Http\Controllers\Participant\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'can:access-participant-dashboard'])
    ->prefix('participant')
    ->name('participant.')
    ->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
    });
