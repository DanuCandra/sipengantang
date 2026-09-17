<?php

use App\Http\Controllers\DashboardRedirectController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::get('/dashboard', DashboardRedirectController::class)
    ->middleware(['auth', 'active'])
    ->name('dashboard');

require __DIR__.'/auth.php';
require __DIR__.'/owner.php';
require __DIR__.'/admin.php';
require __DIR__.'/judge.php';
require __DIR__.'/participant.php';
