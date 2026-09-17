<?php

namespace App\Http\Controllers\Admin;

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
            'area' => 'Admin',
            'headline' => 'Ruang kerja penyelenggara',
            'description' => 'Kelola persiapan lomba, peserta, dan operasional gantangan secara tertib.',
            'userName' => $user->name,
            'userStatus' => $user->status->label(),
            'roles' => $user->roles->map(fn ($role): string => $role->role->label())->all(),
        ]);
    }
}
