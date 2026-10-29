<?php

namespace App\Domain\Certificate\Services;

use App\Enums\CompetitionClassStatus;
use App\Enums\RankingRunStatus;
use App\Enums\RankingRunType;
use App\Models\RankingResult;
use Illuminate\Database\Eloquent\Builder;

class CertificateEligibility
{
    /** @return Builder<RankingResult> */
    public function query(): Builder
    {
        return RankingResult::query()->whereNotNull('rank')
            ->whereHas('rankingRun', fn ($query) => $query->where('type', RankingRunType::Final)
                ->where('status', RankingRunStatus::Completed)
                ->whereHas('competitionClass', fn ($class) => $class->where('status', CompetitionClassStatus::Finalized))
                // Prize positions come from the immutable FINAL snapshot, never live prizes.
                ->whereRaw("JSON_CONTAINS(input_snapshot, JSON_OBJECT('rank', ranking_results.rank), '$.prizes')"));
    }
}
