<?php

namespace App\Http\Controllers\Judge;

use App\Domain\Judging\Actions\RecordViolationAction;
use App\Enums\ViolationAction;
use App\Enums\ViolationReasonCode;
use App\Http\Controllers\Controller;
use App\Http\Requests\Judge\DisqualifyParticipantRequest;
use App\Http\Requests\Judge\StoreViolationRequest;
use App\Models\CompetitionClass;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;

class ViolationController extends Controller
{
    public function warning(StoreViolationRequest $request, CompetitionClass $competitionClass, Registration $registration, RecordViolationAction $action): RedirectResponse
    {
        $violation = $action->execute($request->user(), $registration, ViolationAction::Warning, ViolationReasonCode::from($request->validated('reason_code')), $request->validated('reason'), $request->validated('note'), $request->boolean('confirm_disqualification'), $request->ip());

        return redirect()->route('judge.evaluations.edit', [$competitionClass, $registration])->with('success', $violation->action === ViolationAction::Warning ? 'Peringatan berhasil dicatat.' : 'Pelanggaran berulang dicatat. Peserta didiskualifikasi dan riwayat nilai tetap tersimpan.');
    }

    public function disqualify(DisqualifyParticipantRequest $request, CompetitionClass $competitionClass, Registration $registration, RecordViolationAction $action): RedirectResponse
    {
        $action->execute($request->user(), $registration, ViolationAction::Disqualification, ViolationReasonCode::from($request->validated('reason_code')), $request->validated('reason'), $request->validated('note'), $request->boolean('confirm_disqualification'), $request->ip());

        return redirect()->route('judge.evaluations.edit', [$competitionClass, $registration])->with('success', 'Diskualifikasi dicatat. Peserta tidak dapat dinilai lagi dan riwayat nilai tetap tersimpan.');
    }
}
