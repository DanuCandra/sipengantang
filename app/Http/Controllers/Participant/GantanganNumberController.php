<?php

namespace App\Http\Controllers\Participant;

use App\Domain\Registration\Actions\ChooseGantanganNumberAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\ChooseGantanganNumberRequest;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;

class GantanganNumberController extends Controller
{
    public function __invoke(ChooseGantanganNumberRequest $request, Registration $registration, ChooseGantanganNumberAction $action): RedirectResponse
    {
        $action->execute($request->user(), $registration, $request->validated('gantangan_number'), $request->ip());

        return back()->with('success', 'Nomor gantangan berhasil dipilih dan langsung tersimpan tanpa verifikasi Admin.');
    }
}
