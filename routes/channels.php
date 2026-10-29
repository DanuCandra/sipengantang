<?php

use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('event.{eventId}.admin', function (User $user, int $eventId): bool {
    $event = Event::query()->find($eventId);

    return $user->isActive() && $event !== null && $user->can('view', $event);
});
Broadcast::channel('class.{classId}.judge.{judgeId}', function (User $user, int $classId, int $judgeId): bool {
    $class = CompetitionClass::query()->find($classId);

    return $user->isActive() && $user->id === $judgeId && $class !== null && $user->can('judge', $class);
});
Broadcast::channel('user.{userId}', fn (User $user, int $userId): bool => $user->isActive() && $user->id === $userId);
