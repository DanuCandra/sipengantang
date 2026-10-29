<?php

namespace App\Domain\Event\Actions;

use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\EventAdmin;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class AssignEventAdminAction
{
    public function __construct(private ActivityRecorder $activityRecorder) {}

    public function execute(User $actor, Event $event, User $admin, ?string $ipAddress = null): EventAdmin
    {
        return DB::transaction(function () use ($actor, $event, $admin, $ipAddress): EventAdmin {
            Gate::forUser($actor)->authorize('assignAdmin', $event);
            $admin = User::query()->with('roles')->lockForUpdate()->findOrFail($admin->id);
            if (! $admin->hasRole(UserRole::Admin)) {
                throw ValidationException::withMessages(['email' => 'Akun tersebut belum memiliki peran Admin.']);
            }
            if (! $admin->isActive()) {
                throw ValidationException::withMessages(['email' => 'Pilih akun Admin yang masih aktif.']);
            }
            $assignment = EventAdmin::query()->firstOrCreate(['event_id' => $event->id, 'user_id' => $admin->id]);
            $this->activityRecorder->record($actor, ActivityAction::AdminAssigned, $event, ['admin_id' => $admin->id], $ipAddress);

            return $assignment;
        });
    }
}
