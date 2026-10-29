<?php

namespace App\Providers;

use App\Models\Bird;
use App\Models\Certificate;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\JudgeAssignment;
use App\Models\JudgeEvaluation;
use App\Models\PaymentSubmission;
use App\Models\Prize;
use App\Models\RankingRun;
use App\Models\RankingTieResolution;
use App\Models\Registration;
use App\Models\User;
use App\Models\Violation;
use App\View\Composers\DashboardNavigationComposer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Model::preventLazyLoading(! app()->isProduction());
        View::composer('components.layouts.dashboard', DashboardNavigationComposer::class);

        Relation::enforceMorphMap([
            'bird' => Bird::class,
            'certificate' => Certificate::class,
            'competition_class' => CompetitionClass::class,
            'event' => Event::class,
            'judge_assignment' => JudgeAssignment::class,
            'judge_evaluation' => JudgeEvaluation::class,
            'payment_submission' => PaymentSubmission::class,
            'prize' => Prize::class,
            'registration' => Registration::class,
            'ranking_run' => RankingRun::class,
            'ranking_tie_resolution' => RankingTieResolution::class,
            'user' => User::class,
            'violation' => Violation::class,
        ]);
    }
}
