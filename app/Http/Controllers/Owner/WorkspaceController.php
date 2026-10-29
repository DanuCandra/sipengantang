<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WorkspaceController extends Controller
{
    public function __invoke(Request $request, string $section): View
    {
        $events = $request->user()->ownedEvents()->withCount(['admins', 'competitionClasses'])->latest('start_at')->latest('id')->paginate(12);

        return view('owner.workspace', compact('events', 'section'));
    }
}
