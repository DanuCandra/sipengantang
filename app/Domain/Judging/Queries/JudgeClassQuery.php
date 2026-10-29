<?php

namespace App\Domain\Judging\Queries;

use App\Enums\RegistrationStatus;
use App\Models\CompetitionClass;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class JudgeClassQuery
{
    public function forJudge(User $judge): Builder
    {
        return CompetitionClass::query()
            ->whereHas('judgeAssignments', fn ($query) => $query->active()->where('judge_id', $judge->id))
            ->with('event:id,name,slug,location_name,status,published_at')
            ->withCount([
                'registrations as eligible_count' => fn ($query) => $query->where('status', RegistrationStatus::Verified),
                'registrations as submitted_count' => fn ($query) => $query->where('status', RegistrationStatus::Verified)
                    ->whereHas('judgeEvaluations', fn ($evaluation) => $evaluation->where('judge_id', $judge->id)->submitted()),
            ])
            ->orderBy('start_at')->orderBy('id');
    }
}
