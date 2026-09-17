<?php

use App\Http\Controllers\Judge\DashboardController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'can:access-judge-dashboard'])
    ->prefix('judge')
    ->name('judge.')
    ->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
    });
