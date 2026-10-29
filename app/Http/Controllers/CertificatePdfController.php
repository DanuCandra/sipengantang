<?php

namespace App\Http\Controllers;

use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificatePdfController extends Controller
{
    public function __invoke(Request $request, Certificate $certificate): StreamedResponse
    {
        Gate::authorize('view', $certificate);
        abort_unless(Storage::disk('private')->exists($certificate->file_path), 503, 'File piagam belum tersedia. Hubungi panitia untuk memulihkan PDF.');
        $name = 'piagam-'.$certificate->metadata_snapshot['number'].'.pdf';

        return Storage::disk('private')->response($certificate->file_path, $name, ['Content-Type' => 'application/pdf', 'Cache-Control' => 'private, no-store', 'X-Content-Type-Options' => 'nosniff'], $request->boolean('download') ? 'attachment' : 'inline');
    }
}
