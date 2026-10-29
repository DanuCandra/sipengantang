<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Registration;
use App\Models\Violation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(Request $request, Event $event): View
    {
        Gate::authorize('view', $event);
        $registrations = Registration::query()
            ->whereHas('competitionClass', fn ($query) => $query->where('event_id', $event->id))
            ->with(['participant:id,name,email,phone', 'bird:id,name,bird_type,photo_path', 'competitionClass:id,event_id,name'])
            ->when($request->filled('search'), function ($query) use ($request): void {
                $search = $request->string('search')->trim()->toString();
                $query->where(function ($nested) use ($search): void {
                    $nested->where('registration_number', 'like', "%{$search}%")
                        ->orWhereHas('participant', fn ($participant) => $participant->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('bird', fn ($bird) => $bird->where('name', 'like', "%{$search}%"));
                });
            })
            ->latest('registered_at')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.registrations.index', compact('event', 'registrations'));
    }

    public function show(Event $event, Registration $registration): View
    {
        Gate::authorize('view', $event);
        $registration->load(['participant.participantProfile', 'bird', 'competitionClass']);
        abort_unless($registration->competitionClass->event_id === $event->id, 404);
        Gate::authorize('view', $registration);
        $payments = $registration->paymentSubmissions()->latest('id')->paginate(10);
        Gate::authorize('viewAny', [Violation::class, $registration]);
        $violations = $registration->violations()->with('judge:id,name')->latest('id')->paginate(10, ['*'], 'violations_page')->withQueryString();

        return view('admin.registrations.show', compact('event', 'registration', 'payments', 'violations'));
    }
}
