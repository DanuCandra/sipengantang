<?php

namespace App\Domain\Event\Actions;

use App\Domain\Ranking\Services\FinalizedClassGuard;
use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Models\CompetitionClass;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateCompetitionClassAction
{
    public function __construct(private ActivityRecorder $activityRecorder, private FinalizedClassGuard $guard) {}

    /** @param array<string, mixed> $data */
    public function execute(User $actor, CompetitionClass $competitionClass, array $data, ?string $ipAddress = null): CompetitionClass
    {
        return DB::transaction(function () use ($actor, $competitionClass, $data, $ipAddress): CompetitionClass {
            $competitionClass = $this->guard->lockMutable($competitionClass->id);
            $competitionClass->update($data);
            $this->activityRecorder->record($actor, ActivityAction::CompetitionClassUpdated, $competitionClass, [], $ipAddress);

            return $competitionClass->refresh();
        });
    }
}
