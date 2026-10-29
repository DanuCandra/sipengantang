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

class CreateBirdAction
{
    public function __construct(private ActivityRecorder $activityRecorder) {}

    /** @param array<string, mixed> $data */
    public function execute(User $owner, array $data, UploadedFile $photo, ?string $ipAddress = null): Bird
    {
        $photoPath = $photo->store("birds/{$owner->id}", 'public');

        try {
            return DB::transaction(function () use ($owner, $data, $photoPath, $ipAddress): Bird {
                $bird = $owner->birds()->create([...$data, 'photo_path' => $photoPath]);
                $this->activityRecorder->record($owner, ActivityAction::BirdCreated, $bird, [], $ipAddress);

                return $bird;
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($photoPath);

            throw $exception;
        }
    }
}
