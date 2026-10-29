<?php

namespace App\Domain\Event\Actions;

use App\Domain\Ranking\Services\FinalizedClassGuard;
use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Models\CompetitionClass;
use App\Models\Prize;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreatePrizeAction
{
    public function __construct(private ActivityRecorder $activityRecorder, private FinalizedClassGuard $guard) {}

    /** @param array<string, mixed> $data */
    public function execute(User $actor, CompetitionClass $competitionClass, array $data, ?string $ipAddress = null): Prize
    {
        return DB::transaction(function () use ($actor, $competitionClass, $data, $ipAddress): Prize {
            $competitionClass = $this->guard->lockMutable($competitionClass->id);
            $prize = $competitionClass->prizes()->create($data);
            $this->activityRecorder->record($actor, ActivityAction::PrizeCreated, $prize, [], $ipAddress);

            return $prize;
        });
    }
}
