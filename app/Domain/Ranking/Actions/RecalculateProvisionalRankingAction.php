<?php

namespace App\Domain\Ranking\Actions;

use App\Domain\Judging\Services\JudgingStateGuard;
use App\Domain\Ranking\Services\RankingInputBuilder;
use App\Domain\Ranking\Services\RankingService;
use App\Enums\RankingRunType;
use App\Events\RankingUpdated;
use App\Models\CompetitionClass;
use App\Models\RankingRun;
use Illuminate\Support\Facades\DB;

class RecalculateProvisionalRankingAction
{
    public function __construct(private RankingInputBuilder $builder, private RankingService $ranking, private JudgingStateGuard $guard) {}

    public function execute(CompetitionClass $competitionClass): ?RankingRun
    {
        return DB::transaction(function () use ($competitionClass): ?RankingRun {
            $locked = CompetitionClass::query()->lockForUpdate()->findOrFail($competitionClass->id);
            if (! $this->guard->isScoringOpen($locked)) {
                return null;
            }
            $source = $this->builder->build($locked);
            $latest = $locked->rankingRuns()->where('type', RankingRunType::Provisional)->latest('id')->first();
            if ($latest?->source_hash === $source['hash']) {
                return $latest;
            }

            $run = $this->ranking->persist($locked, $source, $this->ranking->calculate($source), RankingRunType::Provisional, null);
            RankingUpdated::dispatch($locked->id);

            return $run;
        });
    }
}
