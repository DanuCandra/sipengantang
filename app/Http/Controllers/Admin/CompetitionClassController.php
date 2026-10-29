<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Event\Actions\CreateCompetitionClassAction;
use App\Domain\Event\Actions\UpdateCompetitionClassAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCompetitionClassRequest;
use App\Models\CompetitionClass;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class CompetitionClassController extends Controller
{
    public function create(Event $event): View
    {
        Gate::authorize('create', [CompetitionClass::class, $event]);

        return view('admin.classes.create', compact('event'));
    }

    public function store(StoreCompetitionClassRequest $request, Event $event, CreateCompetitionClassAction $action): RedirectResponse
    {
        Gate::authorize('create', [CompetitionClass::class, $event]);
        $competitionClass = $action->execute($request->user(), $event, $request->validated(), $request->ip());

        return redirect()->route('admin.events.classes.show', [$event, $competitionClass])->with('success', 'Kelas dan snapshot lima kriteria berhasil dibuat.');
    }

    public function show(Event $event, CompetitionClass $competitionClass): View
    {
        $this->assertNested($event, $competitionClass);
        Gate::authorize('view', $competitionClass);
        $competitionClass->load(['criteria', 'prizes']);

        return view('admin.classes.show', compact('event', 'competitionClass'));
    }

    public function edit(Event $event, CompetitionClass $competitionClass): View
    {
        $this->assertNested($event, $competitionClass);
        Gate::authorize('update', $competitionClass);

        return view('admin.classes.edit', compact('event', 'competitionClass'));
    }

    public function update(StoreCompetitionClassRequest $request, Event $event, CompetitionClass $competitionClass, UpdateCompetitionClassAction $action): RedirectResponse
    {
        $this->assertNested($event, $competitionClass);
        Gate::authorize('update', $competitionClass);
        $action->execute($request->user(), $competitionClass, $request->validated(), $request->ip());

        return redirect()->route('admin.events.classes.show', [$event, $competitionClass])->with('success', 'Informasi kelas berhasil diperbarui.');
    }

    private function assertNested(Event $event, CompetitionClass $competitionClass): void
    {
        abort_unless($competitionClass->event_id === $event->id, 404);
    }
}
