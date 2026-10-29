<?php

namespace App\Domain\Registration\Actions;

use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Models\Bird;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class UpdateBirdAction
{
    public function __construct(private ActivityRecorder $activityRecorder) {}

    /** @param array<string, mixed> $data */
    public function execute(User $owner, Bird $bird, array $data, ?UploadedFile $photo = null, ?string $ipAddress = null): Bird
    {
        $oldPhotoPath = $bird->photo_path;
        $newPhotoPath = $photo?->store("birds/{$owner->id}", 'public');

        try {
            $updatedBird = DB::transaction(function () use ($owner, $bird, $data, $newPhotoPath, $ipAddress): Bird {
                $bird->update([...$data, ...($newPhotoPath ? ['photo_path' => $newPhotoPath] : [])]);
                $this->activityRecorder->record($owner, ActivityAction::BirdUpdated, $bird, [], $ipAddress);

                return $bird->refresh();
            });
        } catch (Throwable $exception) {
            if ($newPhotoPath) {
                Storage::disk('public')->delete($newPhotoPath);
            }

            throw $exception;
        }

        if ($newPhotoPath && $oldPhotoPath) {
            Storage::disk('public')->delete($oldPhotoPath);
        }

        return $updatedBird;
    }
}
