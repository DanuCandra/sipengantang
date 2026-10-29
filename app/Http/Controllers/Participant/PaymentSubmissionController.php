<?php

namespace App\Http\Controllers\Participant;

use App\Domain\Payment\Actions\SubmitPaymentAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Participant\SubmitPaymentProofRequest;
use App\Models\Registration;
use Illuminate\Http\RedirectResponse;

class PaymentSubmissionController extends Controller
{
    public function store(SubmitPaymentProofRequest $request, Registration $registration, SubmitPaymentAction $action): RedirectResponse
    {
        $action->execute($request->user(), $registration, $request->integer('amount'), $request->file('proof'), $request->ip());

        return redirect()->route('participant.registrations.show', $registration)->with('success', 'Bukti pembayaran dikirim. Panitia akan memeriksa pembayaran Anda.');
    }
}
