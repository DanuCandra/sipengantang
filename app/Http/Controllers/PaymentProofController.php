<?php

namespace App\Http\Controllers;

use App\Models\PaymentSubmission;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PaymentProofController extends Controller
{
    public function __invoke(PaymentSubmission $paymentSubmission): StreamedResponse
    {
        Gate::authorize('view', $paymentSubmission);
        $disk = Storage::disk('private');
        abort_unless($disk->exists($paymentSubmission->proof_path), 404, 'Bukti pembayaran tidak ditemukan.');

        return $disk->response($paymentSubmission->proof_path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
            'Content-Security-Policy' => "default-src 'none'; sandbox",
        ]);
    }
}
