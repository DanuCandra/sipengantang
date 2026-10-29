<?php

namespace App\Http\Controllers\Participant;

use App\Domain\Registration\Actions\CreateRegistrationAction;
use App\Enums\CompetitionClassStatus;
use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Enums\ViolationAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\StoreRegistrationRequest;
use App\Models\Bird;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Registration::class);
        $registrations = $request->user()->registrations()
            ->with(['bird:id,name,bird_type,photo_path', 'competitionClass.event'])
            ->latest('registered_at')
            ->latest('id')
            ->paginate(12);

        return view('participant.registrations.index', compact('registrations'));
    }

    public function create(Request $request, Event $event): View
    {
        Gate::authorize('create', Registration::class);
        abort_if($event->published_at === null || $event->status !== EventStatus::Upcoming, 404);

        $classes = $event->competitionClasses()
            ->where('status', CompetitionClassStatus::Upcoming)
            ->with('prizes')
            ->withCount(['registrations as occupied_slots' => fn ($query) => $query->where('status', '!=', RegistrationStatus::Cancelled)])
            ->orderBy('start_at')
            ->orderBy('id')
            ->get();

        $selectedClassId = $request->integer('class');
        $selectedClass = $selectedClassId > 0
            ? $classes->firstWhere('id', $selectedClassId)
            : $classes->first();

        abort_if($selectedClassId > 0 && $selectedClass === null, 404);

        $birds = $selectedClass
            ? $request->user()->birds()->where('bird_type', $selectedClass->bird_type)->orderBy('name')->get()
            : collect();

        return view('participant.registrations.create', compact('event', 'classes', 'selectedClass', 'birds'));
    }

    public function store(StoreRegistrationRequest $request, CreateRegistrationAction $action): RedirectResponse
    {
        Gate::authorize('create', Registration::class);
        $competitionClass = CompetitionClass::query()->findOrFail($request->integer('competition_class_id'));
        $bird = Bird::query()->findOrFail($request->integer('bird_id'));
        $registration = $action->execute($request->user(), $competitionClass, $bird, $request->validated('gantangan_number'), $request->ip());

        return redirect()->route('participant.registrations.show', $registration)->with('success', 'Pendaftaran berhasil dibuat.');
    }

    public function show(Registration $registration): View
    {
        Gate::authorize('view', $registration);
        $registration->load(['bird', 'competitionClass.event', 'competitionClass.prizes']);
        $registration->loadCount(['violations as warning_count' => fn ($query) => $query->where('action', ViolationAction::Warning)]);
        $payments = $registration->paymentSubmissions()->latest('id')->paginate(10);
        $canChooseGantanganNumber = $registration->gantangan_number === null
            && $registration->competitionClass->status === CompetitionClassStatus::Upcoming
            && $registration->competitionClass->event->status === EventStatus::Upcoming
            && $registration->competitionClass->event->published_at !== null
            && in_array($registration->status, [RegistrationStatus::PendingPayment, RegistrationStatus::WaitingVerification, RegistrationStatus::PaymentRejected, RegistrationStatus::Verified], true);

        return view('participant.registrations.show', compact('registration', 'payments', 'canChooseGantanganNumber'));
    }
}
