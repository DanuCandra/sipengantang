<?php

namespace App\Domain\Registration\Actions;

use App\Domain\Shared\Services\ActivityRecorder;
use App\Enums\ActivityAction;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class UpdateParticipantProfileAction
{
    public function __construct(private ActivityRecorder $activityRecorder) {}

    /** @param array<string, mixed> $data */
    public function execute(User $participant, array $data, ?string $ipAddress = null): User
    {
        return DB::transaction(function () use ($participant, $data, $ipAddress): User {
            $participant->update(Arr::only($data, ['name', 'phone']));
            $participant->participantProfile()->updateOrCreate(
                ['user_id' => $participant->id],
                Arr::only($data, ['address', 'city', 'province', 'postal_code']),
            );
            $this->activityRecorder->record($participant, ActivityAction::ParticipantProfileUpdated, $participant, [], $ipAddress);

            return $participant->refresh()->load('participantProfile');
        });
    }
}
