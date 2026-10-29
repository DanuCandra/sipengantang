<?php

use App\Http\Controllers\Participant\BirdController;
use App\Http\Controllers\Participant\CertificateController;
use App\Http\Controllers\Participant\DashboardController;
use App\Http\Controllers\Participant\EventController;
use App\Http\Controllers\Participant\GantanganNumberController;
use App\Http\Controllers\Participant\PaymentSubmissionController;
use App\Http\Controllers\Participant\ProfileController;
use App\Http\Controllers\Participant\RegistrationController;
use App\Http\Controllers\Participant\RegistrationProofController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'can:access-participant-dashboard'])
    ->prefix('participant')
    ->name('participant.')
    ->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/certificates', [CertificateController::class, 'index'])->name('certificates.index');
        Route::get('/certificates/live', [CertificateController::class, 'live'])->middleware('throttle:60,1')->name('certificates.live');
        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        Route::get('/events/{event:slug}/register', [RegistrationController::class, 'create'])->name('registrations.create');
        Route::resource('birds', BirdController::class)->except(['destroy']);
        Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
        Route::post('/registrations', [RegistrationController::class, 'store'])->name('registrations.store');
        Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');
        Route::post('/registrations/{registration}/gantangan-number', GantanganNumberController::class)->middleware('throttle:20,1')->name('registrations.gantangan-number');
        Route::post('/registrations/{registration}/payments', [PaymentSubmissionController::class, 'store'])->middleware('throttle:10,1')->name('registrations.payments.store');
        Route::get('/registrations/{registration}/proof', RegistrationProofController::class)->middleware('throttle:20,1')->name('registrations.proof');
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
    });
