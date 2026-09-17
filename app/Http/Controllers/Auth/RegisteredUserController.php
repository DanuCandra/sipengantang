<?php

namespace App\Http\Controllers\Auth;

use App\Domain\Identity\Actions\RegisterParticipantAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterParticipantRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(
        RegisterParticipantRequest $request,
        RegisterParticipantAction $registerParticipant,
    ): RedirectResponse {
        $user = $registerParticipant->execute($request->validated());

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
