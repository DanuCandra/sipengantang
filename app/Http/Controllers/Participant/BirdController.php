<?php

namespace App\Http\Controllers\Participant;

use App\Domain\Registration\Actions\CreateBirdAction;
use App\Domain\Registration\Actions\UpdateBirdAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\StoreBirdRequest;
use App\Http\Requests\Participant\UpdateBirdRequest;
use App\Models\Bird;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BirdController extends Controller
{
    public function index(Request $request): View
    {
        Gate::authorize('viewAny', Bird::class);
        $birds = $request->user()->birds()->withCount('registrations')->latest('id')->paginate(12);

        return view('participant.birds.index', compact('birds'));
    }

    public function create(): View
    {
        Gate::authorize('create', Bird::class);

        return view('participant.birds.create');
    }

    public function store(StoreBirdRequest $request, CreateBirdAction $action): RedirectResponse
    {
        Gate::authorize('create', Bird::class);
        $bird = $action->execute($request->user(), $request->safe()->except('photo'), $request->file('photo'), $request->ip());

        return redirect()->route('participant.birds.show', $bird)->with('success', 'Profil burung berhasil ditambahkan.');
    }

    public function show(Bird $bird): View
    {
        Gate::authorize('view', $bird);
        $bird->load(['registrations' => fn ($query) => $query->with('competitionClass.event')->latest('registered_at')]);

        return view('participant.birds.show', compact('bird'));
    }

    public function edit(Bird $bird): View
    {
        Gate::authorize('update', $bird);

        return view('participant.birds.edit', compact('bird'));
    }

    public function update(UpdateBirdRequest $request, Bird $bird, UpdateBirdAction $action): RedirectResponse
    {
        Gate::authorize('update', $bird);
        $action->execute($request->user(), $bird, $request->safe()->except('photo'), $request->file('photo'), $request->ip());

        return redirect()->route('participant.birds.show', $bird)->with('success', 'Profil burung berhasil diperbarui.');
    }
}
