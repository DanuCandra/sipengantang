<?php

namespace App\Domain\Event\Actions;

use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Enums\EventStatus;
use App\Models\Event;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class CreateEventAction
{
    public function __construct(private ActivityRecorder $activityRecorder) {}

    /** @param array<string, mixed> $data */
    public function execute(User $owner, array $data, ?UploadedFile $banner = null, ?string $ipAddress = null): Event
    {
        $bannerPath = $banner?->store('event-banners', 'public');

        try {
            return DB::transaction(function () use ($owner, $data, $bannerPath, $ipAddress): Event {
                $event = Event::query()->create([
                    ...$data,
                    'owner_id' => $owner->id,
                    'slug' => $this->uniqueSlug($data['name']),
                    'banner_path' => $bannerPath,
                    'status' => EventStatus::Draft,
                ]);

                $this->activityRecorder->record($owner, ActivityAction::EventCreated, $event, ['name' => $event->name], $ipAddress);

                return $event;
            });
        } catch (Throwable $exception) {
            if ($bannerPath) {
                Storage::disk('public')->delete($bannerPath);
            }

            throw $exception;
        }
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'event';
        $slug = $base;
        $suffix = 2;

        while (Event::query()->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix++;
        }

        return $slug;
    }
}
