<?php

namespace App\Http\Controllers\Participant;

use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing('participantProfile');

        $registrations = $user->registrations()
            ->with(['bird:id,name,bird_type,photo_path', 'competitionClass.event'])
            ->latest('registered_at')
            ->limit(4)
            ->get();

        $summary = [
            'birds' => $user->birds()->count(),
            'registrations' => $user->registrations()->count(),
            'pending' => $user->registrations()->where('status', RegistrationStatus::PendingPayment)->count(),
            'verified' => $user->registrations()->where('status', RegistrationStatus::Verified)->count(),
        ];

        $nextEvent = Event::query()
            ->whereNotNull('published_at')
            ->where('status', EventStatus::Upcoming)
            ->orderBy('start_at')
            ->first();

        return view('participant.dashboard', compact('user', 'registrations', 'summary', 'nextEvent'));
    }
}
