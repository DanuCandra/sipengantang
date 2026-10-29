<?php

namespace App\Domain\Event\Actions;

use App\Domain\Ranking\Services\FinalizedClassGuard;
use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Models\Prize;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RemovePrizeAction
{
    public function __construct(private ActivityRecorder $activityRecorder, private FinalizedClassGuard $guard) {}

    public function execute(User $actor, Prize $prize, ?string $ipAddress = null): void
    {
        DB::transaction(function () use ($actor, $prize, $ipAddress): void {
            $this->guard->lockMutable($prize->competition_class_id);
            $this->activityRecorder->record($actor, ActivityAction::PrizeRemoved, $prize, ['title' => $prize->title], $ipAddress);
            $prize->delete();
        });
    }
}
