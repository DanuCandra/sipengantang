<?php

namespace App\Domain\Judging\Actions;

use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\CompetitionClassStatus;
use App\Enums\EventStatus;
use App\Events\JudgingStateChanged;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;

class StartClassJudgingAction
{
    public function __construct(private ActivityRecorder $activityRecorder) {}

    public function execute(User $admin, CompetitionClass $competitionClass, ?string $ipAddress = null): void
    {
        DB::transaction(function () use ($admin, $competitionClass, $ipAddress): void {
            $event = Event::query()->lockForUpdate()->findOrFail($competitionClass->event_id);
            $lockedClass = CompetitionClass::query()->lockForUpdate()->findOrFail($competitionClass->id);
            Gate::forUser($admin)->authorize('manageJudging', $lockedClass);

            if ($lockedClass->status !== CompetitionClassStatus::Upcoming || $event->published_at === null
                || ! in_array($event->status, [EventStatus::Upcoming, EventStatus::Ongoing], true)) {
                throw ValidationException::withMessages(['judging' => 'Penjurian hanya dapat dimulai pada kelas akan datang dalam event yang dipublikasikan.']);
            }
            if ($lockedClass->criteria()->count() !== 5) {
                throw ValidationException::withMessages(['judging' => 'Snapshot lima kriteria kelas belum lengkap.']);
            }

            if ($event->status === EventStatus::Upcoming) {
                $event->update(['status' => EventStatus::Ongoing]);
                $this->activityRecorder->record($admin, ActivityAction::EventUpdated, $event, ['status' => EventStatus::Ongoing->value], $ipAddress);
            }
            $lockedClass->update(['status' => CompetitionClassStatus::Ongoing]);
            $this->activityRecorder->record($admin, ActivityAction::JudgingStarted, $lockedClass, ['event_id' => $event->id], $ipAddress);
            JudgingStateChanged::dispatch($lockedClass->id);
        });
    }
}
