<?php

namespace App\Http\Controllers\Participant;

use App\Domain\Certificate\Services\CertificateEligibility;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CertificateController extends Controller
{
    public function index(Request $request, CertificateEligibility $eligibility): View
    {
        $results = $eligibility->query()->whereHas('registration', fn ($query) => $query->where('participant_id', $request->user()->id))
            ->with(['certificate', 'rankingRun:id,input_snapshot,finalized_at'])->orderByDesc('id')->paginate(12);
        $available = $results->getCollection()->filter(fn ($result) => $result->certificate && Storage::disk('private')->exists($result->certificate->file_path))->pluck('id')->all();

        return view('participant.certificates.index', compact('results', 'available'));
    }

    public function live(Request $request, CertificateEligibility $eligibility): JsonResponse
    {
        $data = $request->validate(['results' => ['sometimes', 'array', 'max:12'], 'results.*' => ['integer', 'distinct', 'min:1']]);
        $results = $eligibility->query()->whereKey($data['results'] ?? [])->whereHas('registration', fn ($query) => $query->where('participant_id', $request->user()->id))->with('certificate')->get(['id', 'registration_id']);

        return response()->json(['certificates' => $results->map(function ($result): array {
            $ready = $result->certificate && Storage::disk('private')->exists($result->certificate->file_path);

            return ['result_id' => $result->id, 'ready' => (bool) $ready, 'preview_url' => $ready ? route('certificates.pdf', $result->certificate) : null, 'download_url' => $ready ? route('certificates.pdf', [$result->certificate, 'download' => 1]) : null];
        })->all()])->header('Cache-Control', 'private, no-store');
    }
}
