<?php

namespace App\Domain\Event\Actions;

use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Models\Event;
use App\Models\EventAdmin;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RemoveEventAdminAction
{
    public function __construct(private ActivityRecorder $activityRecorder) {}

    public function execute(User $actor, Event $event, EventAdmin $assignment, ?string $ipAddress = null): void
    {
        DB::transaction(function () use ($actor, $event, $assignment, $ipAddress): void {
            $adminId = $assignment->user_id;
            $assignment->delete();
            $this->activityRecorder->record($actor, ActivityAction::AdminRemoved, $event, ['admin_id' => $adminId], $ipAddress);
        });
    }
}
