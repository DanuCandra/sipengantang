<?php

namespace App\Domain\Shared\Services;

use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ActivityRecorder
{
    /** @param array<string, mixed> $metadata */
    public function record(User $actor, ActivityAction $action, Model $subject, array $metadata = [], ?string $ipAddress = null): void
    {
        ActivityLog::query()->create([
            'user_id' => $actor->id,
            'action' => $action,
            'subject_type' => $subject->getMorphClass(),
            'subject_id' => $subject->getKey(),
            'metadata' => $metadata,
            'ip_address' => $ipAddress,
        ]);
    }
}
