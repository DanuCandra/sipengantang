<?php

namespace App\Http\Controllers\Participant;

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
            'area' => 'Peserta',
            'headline' => 'Pusat aktivitas peserta',
            'description' => 'Siapkan profil dan pantau keikutsertaan lomba dari satu tempat yang mudah digunakan.',
            'userName' => $user->name,
            'userStatus' => $user->status->label(),
            'roles' => $user->roles->map(fn ($role): string => $role->role->label())->all(),
        ]);
    }
}
