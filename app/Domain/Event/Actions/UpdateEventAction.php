<?php

namespace App\Domain\Event\Actions;

use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UpdateEventAction
{
    public function __construct(private ActivityRecorder $activityRecorder) {}

    /** @param array<string, mixed> $data */
    public function execute(User $actor, Event $event, array $data, ?UploadedFile $banner = null, ?string $ipAddress = null): Event
    {
        $oldBannerPath = $event->banner_path;
        $newBannerPath = $banner?->store('event-banners', 'public');

        try {
            $updatedEvent = DB::transaction(function () use ($actor, $event, $data, $newBannerPath, $ipAddress): Event {
                $event->update([
                    ...$data,
                    ...($newBannerPath ? ['banner_path' => $newBannerPath] : []),
                ]);
                $this->activityRecorder->record($actor, ActivityAction::EventUpdated, $event, [], $ipAddress);

                return $event->refresh();
            });
        } catch (Throwable $exception) {
            if ($newBannerPath) {
                Storage::disk('public')->delete($newBannerPath);
            }

            throw $exception;
        }

        if ($newBannerPath && $oldBannerPath) {
            Storage::disk('public')->delete($oldBannerPath);
        }

        return $updatedEvent;
    }
}
