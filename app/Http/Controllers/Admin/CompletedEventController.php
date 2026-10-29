<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Event\Actions\CompleteEventAction;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CompletedEventController extends Controller
{
    public function __invoke(Request $request, Event $event, CompleteEventAction $action): RedirectResponse
    {
        $request->validate(['confirmation' => ['required', 'in:SELESAI']], ['confirmation.in' => 'Ketik SELESAI untuk menutup event.']);
        $action->execute($request->user(), $event, $request->ip());

        return back()->with('success', 'Event selesai. Hasil resmi seluruh kelas tetap tersimpan.');
    }
}
