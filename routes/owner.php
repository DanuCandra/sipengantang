<?php

use App\Http\Controllers\LiveClassController;
use App\Http\Controllers\Owner\DashboardController;
use App\Http\Controllers\Owner\EventAdminController;
use App\Http\Controllers\Owner\EventController;
use App\Http\Controllers\Owner\PublishedEventController;
use App\Http\Controllers\Owner\WorkspaceController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'can:access-owner-dashboard'])
    ->prefix('owner')
    ->name('owner.')
    ->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/workspace/{section}', WorkspaceController::class)->whereIn('section', ['admins', 'monitoring'])->name('workspace');
        Route::get('/events/{event}/classes/{competitionClass}/live', [LiveClassController::class, 'admin'])->middleware('throttle:120,1')->name('events.classes.live');
        Route::resource('events', EventController::class)->except(['destroy']);
        Route::post('events/{event}/publish', [PublishedEventController::class, 'store'])->name('events.publish');
        Route::post('events/{event}/admins', [EventAdminController::class, 'store'])->name('events.admins.store');
        Route::post('events/{event}/admins/accounts', [EventAdminController::class, 'storeAccount'])->name('events.admins.accounts.store');
        Route::delete('events/{event}/admins/{eventAdmin}', [EventAdminController::class, 'destroy'])->name('events.admins.destroy');
    });
