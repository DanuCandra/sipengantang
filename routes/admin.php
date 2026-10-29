<?php

use App\Http\Controllers\Admin\AbsentJudgeReplacementController;
use App\Http\Controllers\Admin\CompetitionClassController;
use App\Http\Controllers\Admin\CompletedEventController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EventController;
use App\Http\Controllers\Admin\JudgeAssignmentController;
use App\Http\Controllers\Admin\PaymentSubmissionController;
use App\Http\Controllers\Admin\PrizeController;
use App\Http\Controllers\Admin\RankingController;
use App\Http\Controllers\Admin\RegistrationController;
use App\Http\Controllers\Admin\StartedClassJudgingController;
use App\Http\Controllers\Admin\ViolationController;
use App\Http\Controllers\Admin\WinnerCertificateController;
use App\Http\Controllers\Admin\WorkspaceController;
use App\Http\Controllers\LiveClassController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'active', 'can:access-admin-dashboard'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/dashboard', DashboardController::class)->name('dashboard');
        Route::get('/workspace/{section}', WorkspaceController::class)->whereIn('section', ['overview', 'classes', 'participants', 'payments', 'judges', 'judging', 'results', 'prizes', 'violations', 'settings'])->name('workspace');
        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
        Route::get('/events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
        Route::put('/events/{event}', [EventController::class, 'update'])->name('events.update');
        Route::resource('/events/{event}/classes', CompetitionClassController::class)
            ->parameters(['classes' => 'competitionClass'])
            ->names('events.classes')
            ->except(['index', 'destroy']);
        Route::post('/events/{event}/classes/{competitionClass}/prizes', [PrizeController::class, 'store'])->name('events.classes.prizes.store');
        Route::delete('/events/{event}/classes/{competitionClass}/prizes/{prize}', [PrizeController::class, 'destroy'])->name('events.classes.prizes.destroy');
        Route::get('/events/{event}/registrations', [RegistrationController::class, 'index'])->name('events.registrations.index');
        Route::get('/events/{event}/registrations/{registration}', [RegistrationController::class, 'show'])->name('events.registrations.show');
        Route::get('/events/{event}/payments', [PaymentSubmissionController::class, 'index'])->name('events.payments.index');
        Route::get('/events/{event}/payments/{paymentSubmission}', [PaymentSubmissionController::class, 'show'])->name('events.payments.show');
        Route::patch('/events/{event}/payments/{paymentSubmission}', [PaymentSubmissionController::class, 'update'])->name('events.payments.update');
        Route::get('/events/{event}/classes/{competitionClass}/judges', [JudgeAssignmentController::class, 'index'])->name('events.classes.judges.index');
        Route::get('/events/{event}/classes/{competitionClass}/judges/create', [JudgeAssignmentController::class, 'create'])->name('events.classes.judges.create');
        Route::post('/events/{event}/classes/{competitionClass}/judges', [JudgeAssignmentController::class, 'store'])->name('events.classes.judges.store');
        Route::post('/events/{event}/classes/{competitionClass}/judges/accounts', [JudgeAssignmentController::class, 'storeAccount'])->name('events.classes.judges.accounts');
        Route::post('/events/{event}/classes/{competitionClass}/start-judging', StartedClassJudgingController::class)->name('events.classes.judging.start');
        Route::get('/events/{event}/violations', [ViolationController::class, 'index'])->name('events.violations.index');
        Route::post('/events/{event}/classes/{competitionClass}/judges/{judgeAssignment}/replacement', AbsentJudgeReplacementController::class)->name('events.classes.judges.replace');
        Route::get('/events/{event}/classes/{competitionClass}/ranking', [RankingController::class, 'show'])->name('events.classes.ranking');
        Route::get('/events/{event}/classes/{competitionClass}/live', [LiveClassController::class, 'admin'])->middleware('throttle:120,1')->name('events.classes.live');
        Route::post('/events/{event}/classes/{competitionClass}/ranking/recalculate', [RankingController::class, 'recalculate'])->name('events.classes.ranking.recalculate');
        Route::post('/events/{event}/classes/{competitionClass}/ranking/finalize', [RankingController::class, 'finalize'])->name('events.classes.ranking.finalize');
        Route::post('/events/{event}/classes/{competitionClass}/ranking/{rankingRun}/draw', [RankingController::class, 'draw'])->name('events.classes.ranking.draw');
        Route::post('/events/{event}/complete', CompletedEventController::class)->name('events.complete');
        Route::post('/events/{event}/classes/{competitionClass}/certificates', WinnerCertificateController::class)->middleware('throttle:5,1')->name('events.classes.certificates.generate');
    });
