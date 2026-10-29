<?php

namespace App\Domain\Event\Actions;

use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\CompetitionClassStatus;
use App\Enums\EventStatus;
use App\Enums\UserRole;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class CompleteEventAction
{
    public function __construct(private ActivityRecorder $audit) {}

    public function execute(User $admin, Event $event, ?string $ip = null): void
    {
        DB::transaction(function () use ($admin, $event, $ip): void {
            $event = Event::query()->lockForUpdate()->findOrFail($event->id);
            Gate::forUser($admin)->authorize('view', $event);
            abort_unless($admin->loadMissing('roles')->hasRole(UserRole::Admin) && $event->adminAssignments()->where('user_id', $admin->id)->exists(), 403);
            $classes = $event->competitionClasses()->orderBy('id')->lockForUpdate()->get();
            if ($event->status !== EventStatus::Ongoing || $classes->isEmpty()
                || ! $classes->contains('status', CompetitionClassStatus::Finalized)
                || $classes->contains(fn ($class) => ! in_array($class->status, [CompetitionClassStatus::Finalized, CompetitionClassStatus::Cancelled], true))) {
                throw ValidationException::withMessages(['event' => 'Event hanya dapat diselesaikan jika seluruh kelas sudah final atau dibatalkan, dengan setidaknya satu kelas final.']);
            }
            $event->update(['status' => EventStatus::Completed]);
            $this->audit->record($admin, ActivityAction::EventCompleted, $event, [], $ip);
        });
    }
}
