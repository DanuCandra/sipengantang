<?php

namespace App\Http\Controllers\Owner;

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
            'area' => 'Owner',
            'headline' => 'Kendali strategis sistem',
            'description' => 'Pantau kesiapan operasional, pengelola, dan konfigurasi lintas event dari satu area.',
            'userName' => $user->name,
            'userStatus' => $user->status->label(),
            'roles' => $user->roles->map(fn ($role): string => $role->role->label())->all(),
        ]);
    }
}
