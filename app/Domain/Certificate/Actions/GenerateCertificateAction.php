<?php

namespace App\Domain\Certificate\Actions;

use App\Domain\Certificate\Services\CertificateEligibility;
use App\Domain\Certificate\Services\CertificateRenderer;
use App\Enums\ActivityAction;
use App\Events\CertificateGenerated;
use App\Models\ActivityLog;
use App\Models\Certificate;
use App\Models\RankingResult;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

class GenerateCertificateAction
{
    public function __construct(private CertificateEligibility $eligibility, private CertificateRenderer $renderer) {}

    public function execute(RankingResult $result): Certificate
    {
        $writtenPath = null;
        try {
            return DB::transaction(function () use ($result, &$writtenPath): Certificate {
                $locked = RankingResult::query()->lockForUpdate()->findOrFail($result->id);
                if (! $this->eligibility->query()->whereKey($locked->id)->exists()) {
                    throw ValidationException::withMessages(['certificate' => 'Piagam hanya tersedia untuk peringkat hadiah pada hasil FINAL.']);
                }
                $existing = Certificate::query()->where('ranking_result_id', $locked->id)->first();
                if ($existing && Storage::disk('private')->exists($existing->file_path)) {
                    return $existing;
                }
                $locked->load(['rankingRun.competitionClass.event', 'registration:id,participant_id']);
                $run = $locked->rankingRun;
                $class = $run->competitionClass;
                $metadata = $run->input_snapshot['class'];
                // Stage 7 FINAL runs predate date/organizer snapshots. Freeze their current
                // event date once on first issuance; never mutate the historical FINAL run.
                $snapshot = $existing?->metadata_snapshot ?? [
                    'number' => 'GS-'.str_pad((string) $locked->id, 8, '0', STR_PAD_LEFT),
                    'participant_name' => $locked->entry_snapshot['participant_name'], 'bird_name' => $locked->entry_snapshot['bird_name'],
                    'bird_type' => $locked->entry_snapshot['bird_type'], 'event_name' => $metadata['event_name'], 'class_name' => $metadata['name'],
                    'event_date' => Carbon::parse($metadata['event_start_at'] ?? $class->event->start_at)->translatedFormat('d F Y'),
                    'organizer_name' => $metadata['organizer_name'] ?? config('app.name'), 'rank' => $locked->rank,
                    'finalized_at' => $run->finalized_at->toIso8601String(),
                ];
                $binary = $this->renderer->render($snapshot);
                $path = $existing?->file_path ?? 'certificates/'.$class->event_id.'/'.$class->id.'/'.Str::uuid().'.pdf';
                Storage::disk('private')->put($path, $binary);
                if ($existing) {
                    return $existing;
                }
                $writtenPath = $path;
                $certificate = Certificate::query()->create([
                    'event_id' => $class->event_id, 'competition_class_id' => $class->id, 'registration_id' => $locked->registration_id,
                    'ranking_result_id' => $locked->id, 'rank' => $locked->rank, 'file_path' => $path,
                    'template_version' => CertificateRenderer::VERSION, 'metadata_snapshot' => $snapshot, 'generated_at' => now(),
                ]);
                ActivityLog::query()->create(['user_id' => null, 'action' => ActivityAction::CertificateGenerated, 'subject_type' => 'certificate', 'subject_id' => $certificate->id, 'metadata' => ['ranking_result_id' => $locked->id, 'ranking_run_id' => $run->id]]);
                CertificateGenerated::dispatch($certificate->id, $locked->registration->participant_id);

                return $certificate;
            });
        } catch (Throwable $exception) {
            if ($writtenPath !== null) {
                Storage::disk('private')->delete($writtenPath);
            }
            throw $exception;
        }
    }
}
