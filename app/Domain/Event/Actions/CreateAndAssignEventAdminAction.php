<?php

namespace App\Domain\Event\Actions;

use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CreateAndAssignEventAdminAction
{
    public function __construct(private ActivityRecorder $activityRecorder) {}

    /** @param array<string, mixed> $data */
    public function execute(User $actor, Event $event, array $data, ?string $ipAddress = null): User
    {
        return DB::transaction(function () use ($actor, $event, $data, $ipAddress): User {
            $admin = User::query()->create($data);
            $admin->roles()->create(['role' => UserRole::Admin]);
            $event->adminAssignments()->create(['user_id' => $admin->id]);
            $this->activityRecorder->record($actor, ActivityAction::AdminAssigned, $event, ['admin_id' => $admin->id, 'account_created' => true], $ipAddress);

            return $admin;
        });
    }
}
