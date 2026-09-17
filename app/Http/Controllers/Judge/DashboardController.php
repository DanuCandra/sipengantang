<?php

namespace App\Http\Controllers\Judge;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $user->loadMissing('roles');

        return view('dashboard.index', [
            'area' => 'Juri',
            'headline' => 'Panel penilaian juri',
            'description' => 'Akses penugasan dan lembar penilaian lomba melalui alur yang fokus dan aman.',
            'userName' => $user->name,
            'userStatus' => $user->status->label(),
            'roles' => $user->roles->map(fn ($role): string => $role->role->label())->all(),
        ]);
    }
}
