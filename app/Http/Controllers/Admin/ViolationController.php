<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Judging\Queries\EventViolationQuery;
use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class ViolationController extends Controller
{
    public function index(Event $event, EventViolationQuery $query): View
    {
        Gate::authorize('view', $event);
        $violations = $query->forEvent($event)->paginate(15);

        return view('admin.violations.index', compact('event', 'violations'));
    }
}
