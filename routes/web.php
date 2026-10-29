<?php

use App\Http\Controllers\CertificatePdfController;
use App\Http\Controllers\DashboardRedirectController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LiveClassController;
use App\Http\Controllers\PaymentProofController;
use App\Http\Controllers\PublicClassResultController;
use App\Http\Controllers\PublicEventController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/events', [PublicEventController::class, 'index'])->name('events.index');
Route::get('/events/{event:slug}', [PublicEventController::class, 'show'])->name('events.show');
Route::get('/events/{event:slug}/classes/{competitionClass}/results', PublicClassResultController::class)->name('events.classes.results');
Route::get('/events/{event:slug}/classes/{competitionClass}/live', [LiveClassController::class, 'public'])->middleware('throttle:120,1')->name('events.classes.live');
Route::get('/certificates/{certificate}/pdf', CertificatePdfController::class)->middleware(['auth', 'active', 'throttle:30,1'])->name('certificates.pdf');

Route::get('/dashboard', DashboardRedirectController::class)
    ->middleware(['auth', 'active'])
    ->name('dashboard');

Route::get('/payments/{paymentSubmission}/proof', PaymentProofController::class)
    ->middleware(['auth', 'active'])
    ->name('payments.proof');

require __DIR__.'/auth.php';
require __DIR__.'/owner.php';
require __DIR__.'/admin.php';
require __DIR__.'/judge.php';
require __DIR__.'/participant.php';
