<?php

namespace Tests\Feature\Certificate;

use App\Domain\Certificate\Actions\GenerateCertificateAction;
use App\Domain\Certificate\Services\CertificateEligibility;
use App\Domain\Certificate\Services\CertificateRenderer;
use App\Domain\Ranking\Actions\FinalizeClassResultAction;
use App\Domain\Ranking\Actions\RecalculateProvisionalRankingAction;
use App\Domain\Ranking\Actions\RecordDrawResultAction;
use App\Enums\ActivityAction;
use App\Events\CertificateGenerated;
use App\Jobs\GenerateWinnerCertificates;
use App\Models\ActivityLog;
use App\Models\Certificate;
use App\Models\Prize;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Tests\Concerns\CreatesRankingFixtures;
use Tests\TestCase;

class CertificateWorkflowTest extends TestCase
{
    use CreatesRankingFixtures, LazilyRefreshDatabase;

    private function finalFixture(array $ranks = [1]): array
    {
        $f = $this->rankingFixture();
        foreach ($ranks as $rank) {
            Prize::factory()->for($f['class'], 'competitionClass')->create(['rank' => $rank]);
        }
        $f['run'] = app(FinalizeClassResultAction::class)->execute($f['admin'], $f['class']);

        return $f;
    }

    public function test_finalization_queues_document_job_after_commit_and_job_creates_only_configured_winners(): void
    {
        Queue::fake([GenerateWinnerCertificates::class]);
        $f = $this->finalFixture();
        Queue::assertPushed(GenerateWinnerCertificates::class, fn ($job) => $job->rankingRunId === $f['run']->id && $job->connection === 'database');
        Storage::fake('private');
        Event::fake([CertificateGenerated::class]);

        (new GenerateWinnerCertificates($f['run']->id))->handle(app(CertificateEligibility::class), app(GenerateCertificateAction::class));

        $certificate = Certificate::query()->sole();
        $this->assertSame($f['first']->id, $certificate->registration_id);
        $this->assertSame(1, $certificate->rank);
        $this->assertSame($f['class']->id, $certificate->competition_class_id);
        $this->assertSame($f['event']->id, $certificate->event_id);
        $this->assertStringStartsWith('%PDF-', Storage::disk('private')->get($certificate->file_path));
        $this->assertSame($f['run']->input_snapshot['class']['event_name'], $certificate->metadata_snapshot['event_name']);
        $this->assertDatabaseHas('activity_logs', ['action' => ActivityAction::CertificateGenerated->value, 'subject_id' => $certificate->id]);
        Event::assertDispatched(CertificateGenerated::class, fn ($event) => $event->certificateId === $certificate->id && $event->participantId === $f['first']->participant_id);
    }

    public function test_repeated_job_does_not_overwrite_pdf_metadata_or_duplicate_history(): void
    {
        $f = $this->finalFixture([1, 2]);
        Storage::fake('private');
        $job = new GenerateWinnerCertificates($f['run']->id);
        $job->handle(app(CertificateEligibility::class), app(GenerateCertificateAction::class));
        $original = Certificate::query()->orderBy('id')->get()->toArray();
        $hashes = Certificate::query()->get()->mapWithKeys(fn ($c) => [$c->id => hash('sha256', Storage::disk('private')->get($c->file_path))])->all();
        $this->travel(1)->days();
        $f['first']->bird()->first()->update(['name' => 'LIVE-NAME-CHANGED']);
        $job->handle(app(CertificateEligibility::class), app(GenerateCertificateAction::class));
        $this->assertSame($original, Certificate::query()->orderBy('id')->get()->toArray());
        $this->assertSame($hashes, Certificate::query()->get()->mapWithKeys(fn ($c) => [$c->id => hash('sha256', Storage::disk('private')->get($c->file_path))])->all());
        $this->assertDatabaseCount('certificates', 2);
        $this->assertSame(2, ActivityLog::query()->where('action', ActivityAction::CertificateGenerated)->count());
    }

    public function test_missing_file_is_restored_from_certificate_snapshot_not_live_names(): void
    {
        $f = $this->finalFixture();
        Storage::fake('private');
        $result = $f['run']->results()->where('rank', 1)->sole();
        $certificate = app(GenerateCertificateAction::class)->execute($result)->fresh();
        $original = $certificate->toArray();
        Storage::disk('private')->delete($certificate->file_path);
        $f['first']->bird()->first()->update(['name' => 'NOT-THE-CERTIFICATE-NAME']);
        app(GenerateCertificateAction::class)->execute($result);
        $this->assertSame($original, $certificate->fresh()->toArray());
        Storage::disk('private')->assertExists($certificate->file_path);
        $this->assertDatabaseCount('certificates', 1);
    }

    public function test_provisional_non_winner_and_no_prize_cannot_get_certificate(): void
    {
        $f = $this->rankingFixture();
        Storage::fake('private');
        $provisional = app(RecalculateProvisionalRankingAction::class)->execute($f['class']);
        try {
            app(GenerateCertificateAction::class)->execute($provisional->results()->first());
            $this->fail('Provisional must be rejected');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('certificate', $exception->errors());
        }
        $run = app(FinalizeClassResultAction::class)->execute($f['admin'], $f['class']);
        $this->assertSame(0, app(CertificateEligibility::class)->query()->count());
        (new GenerateWinnerCertificates($run->id))->handle(app(CertificateEligibility::class), app(GenerateCertificateAction::class));
        $this->assertDatabaseCount('certificates', 0);
        $this->assertSame([], Storage::disk('private')->allFiles());
    }

    public function test_render_failure_does_not_create_certificate_or_modify_final_result(): void
    {
        $f = $this->finalFixture();
        Storage::fake('private');
        $result = $f['run']->results()->where('rank', 1)->sole();
        $original = $result->toArray();
        $this->mock(CertificateRenderer::class, fn ($mock) => $mock->shouldReceive('render')->once()->andThrow(new RuntimeException('render failed')));
        try {
            app(GenerateCertificateAction::class)->execute($result);
            $this->fail('Expected render failure');
        } catch (RuntimeException $exception) {
            $this->assertSame('render failed', $exception->getMessage());
        }
        $this->assertDatabaseCount('certificates', 0);
        $this->assertSame($original, $result->fresh()->toArray());
        $this->assertSame([], Storage::disk('private')->allFiles());
    }

    public function test_legacy_final_without_date_snapshot_is_frozen_once_on_first_issuance(): void
    {
        $f = $this->finalFixture();
        $input = $f['run']->input_snapshot;
        unset($input['class']['event_start_at'], $input['class']['organizer_name']);
        // Simulate an existing Stage 7 record, not an application mutation endpoint.
        DB::table('ranking_runs')->where('id', $f['run']->id)->update(['input_snapshot' => json_encode($input)]);
        Storage::fake('private');
        $certificate = app(GenerateCertificateAction::class)->execute($f['run']->results()->where('rank', 1)->sole());
        $this->assertSame($f['event']->start_at->translatedFormat('d F Y'), $certificate->metadata_snapshot['event_date']);
        $this->assertSame(config('app.name'), $certificate->metadata_snapshot['organizer_name']);
    }

    public function test_certificate_uses_official_dice_winner_not_shared_mathematical_rank(): void
    {
        $f = $this->rankingFixture(tied: true);
        Prize::factory()->for($f['class'], 'competitionClass')->create(['rank' => 1]);
        $run = app(RecalculateProvisionalRankingAction::class)->execute($f['class']);
        $ids = [$f['first']->id, $f['second']->id];
        sort($ids);
        app(RecordDrawResultAction::class)->execute($f['admin'], $f['class'], $run, hash('sha256', json_encode($ids)), [$f['second']->id, $f['first']->id], 'Dadu peserta kedua tertinggi.');
        $final = app(FinalizeClassResultAction::class)->execute($f['admin'], $f['class']);
        Storage::fake('private');
        (new GenerateWinnerCertificates($final->id))->handle(app(CertificateEligibility::class), app(GenerateCertificateAction::class));
        $this->assertSame($f['second']->id, Certificate::query()->sole()->registration_id);
        $this->assertSame(1, Certificate::query()->sole()->rank);
    }
}
