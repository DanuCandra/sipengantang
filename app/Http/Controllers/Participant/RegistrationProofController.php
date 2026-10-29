<?php

namespace App\Http\Controllers\Participant;

use App\Domain\Registration\Services\RegistrationProofRenderer;
use App\Enums\PaymentStatus;
use App\Enums\RegistrationStatus;
use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;

class RegistrationProofController extends Controller
{
    public function __invoke(Request $request, Registration $registration, RegistrationProofRenderer $renderer): Response
    {
        Gate::authorize('downloadProof', $registration);
        abort_unless($registration->status === RegistrationStatus::Verified
            && $registration->paymentSubmissions()->where('status', PaymentStatus::Verified)->exists(), 403, 'Bukti resmi hanya tersedia untuk pendaftaran terverifikasi.');

        $pdf = $renderer->render($registration);
        $filename = 'bukti-'.$registration->registration_number.'.pdf';
        $response = $request->boolean('download') ? $pdf->download($filename) : $pdf->stream($filename);
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
