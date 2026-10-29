<?php

namespace App\Http\Controllers\Judge;

use App\Domain\Judging\Queries\JudgeClassQuery;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, JudgeClassQuery $query): View
    {
        $user = $request->user();
        $user->loadMissing('roles');

        $classes = $query->forJudge($user)->paginate(12);

        return view('judge.classes.index', compact('classes'));
    }
}
