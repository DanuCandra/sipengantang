<?php

namespace App\Http\Controllers\Participant;

use App\Domain\Registration\Actions\UpdateParticipantProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\UpdateProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $participant = $request->user()->loadMissing('participantProfile');

        return view('participant.profile.edit', compact('participant'));
    }

    public function update(UpdateProfileRequest $request, UpdateParticipantProfileAction $action): RedirectResponse
    {
        $action->execute($request->user(), $request->validated(), $request->ip());

        return back()->with('success', 'Profil peserta berhasil diperbarui.');
    }
}
