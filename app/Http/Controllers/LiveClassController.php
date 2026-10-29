<?php

namespace App\Http\Controllers;

use App\Domain\Judging\Services\LiveClassSnapshot;
use App\Enums\EventStatus;
use App\Models\CompetitionClass;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class LiveClassController extends Controller
{
    private function response(array $data): JsonResponse
    {
        return response()->json($data)->header('Cache-Control', 'private, no-store');
    }

    public function public(Event $event, CompetitionClass $competitionClass, LiveClassSnapshot $snapshot): JsonResponse
    {
        abort_unless($competitionClass->event_id === $event->id && $event->published_at && ! in_array($event->status, [EventStatus::Draft, EventStatus::Cancelled], true), 404);

        return $this->response($snapshot->publicData($competitionClass));
    }

    public function admin(Event $event, CompetitionClass $competitionClass, LiveClassSnapshot $snapshot): JsonResponse
    {
        abort_unless($competitionClass->event_id === $event->id, 404);
        Gate::authorize('view', $competitionClass);

        return $this->response($snapshot->adminData($competitionClass));
    }

    public function judge(Request $request, CompetitionClass $competitionClass, LiveClassSnapshot $snapshot): JsonResponse
    {
        Gate::authorize('judge', $competitionClass);
        $data = $request->validate(['registrations' => ['sometimes', 'array', 'max:15'], 'registrations.*' => ['integer', 'min:1', 'distinct']]);

        return $this->response($snapshot->judgeData($competitionClass, $request->user()->id, $data['registrations'] ?? []));
    }
}
