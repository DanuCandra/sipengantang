<?php

use App\Http\Controllers\Judge\CompetitionClassController;
use App\Http\Controllers\Judge\DashboardController;
use App\Http\Controllers\Judge\EvaluationController;
use App\Http\Controllers\Judge\ViolationController;
use App\Http\Controllers\LiveClassController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'can:access-judge-dashboard'])
    ->prefix('judge')
    ->name('judge.')
    ->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/classes', [CompetitionClassController::class, 'index'])->name('classes.index');
        Route::get('/classes/{competitionClass}/live', [LiveClassController::class, 'judge'])->middleware('throttle:120,1')->name('classes.live');
        Route::get('/classes/{competitionClass}/judging', [CompetitionClassController::class, 'show'])->name('classes.show');
        Route::get('/classes/{competitionClass}/registrations/{registration}/evaluation', [EvaluationController::class, 'edit'])->name('evaluations.edit');
        Route::post('/classes/{competitionClass}/registrations/{registration}/draft', [EvaluationController::class, 'draft'])->name('evaluations.draft');
        Route::post('/classes/{competitionClass}/registrations/{registration}/submit', [EvaluationController::class, 'submit'])->name('evaluations.submit');
        Route::post('/classes/{competitionClass}/registrations/{registration}/warnings', [ViolationController::class, 'warning'])->name('warnings.store');
        Route::post('/classes/{competitionClass}/registrations/{registration}/disqualification', [ViolationController::class, 'disqualify'])->name('disqualifications.store');
    });
