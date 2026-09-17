<?php

use App\Http\Controllers\Owner\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'can:access-owner-dashboard'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
    });
