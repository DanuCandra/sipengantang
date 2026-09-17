<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->loadMissing('roles');

        foreach (UserRole::cases() as $role) {
            if ($user->hasRole($role)) {
                return redirect()->route($role->dashboardRoute());
            }
        }

        abort(403, 'Akun belum memiliki peran yang dapat mengakses dashboard.');
    }
}
