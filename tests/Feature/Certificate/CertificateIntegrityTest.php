<?php

namespace Tests\Feature\Certificate;

use App\Domain\Certificate\Actions\GenerateCertificateAction;
use App\Domain\Ranking\Actions\FinalizeClassResultAction;
use App\Enums\ActivityAction;
use App\Models\ActivityLog;
use App\Models\Prize;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\Concerns\CreatesRankingFixtures;
use Tests\TestCase;

class CertificateIntegrityTest extends TestCase
{
    use CreatesRankingFixtures, LazilyRefreshDatabase;

    public function test_database_or_audit_failure_rolls_back_record_and_removes_only_newly_generated_file(): void
    {
        $f = $this->rankingFixture();
        Prize::factory()->for($f['class'], 'competitionClass')->create(['rank' => 1]);
        $run = app(FinalizeClassResultAction::class)->execute($f['admin'], $f['class']);
        Storage::fake('private');
        Storage::disk('private')->put('existing-file.txt', 'preserve');
        Event::listen('eloquent.creating: '.ActivityLog::class, function (ActivityLog $log): void {
            if ($log->action === ActivityAction::CertificateGenerated) {
                throw new RuntimeException('audit failed');
            }
        });
        try {
            app(GenerateCertificateAction::class)->execute($run->results()->where('rank', 1)->sole());
            $this->fail('Expected audit failure');
        } catch (RuntimeException $exception) {
            $this->assertSame('audit failed', $exception->getMessage());
        }
        $this->assertDatabaseCount('certificates', 0);
        $this->assertSame(['existing-file.txt'], Storage::disk('private')->allFiles());
    }

    public function test_template_escapes_names_and_has_no_fake_signature_or_private_data(): void
    {
        $name = '<script>alert("name")</script>';
        $this->view('pdf.certificate', ['certificate' => ['number' => 'GS-TEST', 'participant_name' => $name, 'bird_name' => 'Panglima', 'bird_type' => 'Murai Batu', 'event_name' => 'Event FINAL', 'class_name' => 'Kelas Utama', 'event_date' => '29 Oktober 2026', 'organizer_name' => 'GantangScore', 'rank' => 1]])
            ->assertSee($name)->assertDontSee($name, false)->assertSee('PIAGAM PENGHARGAAN')->assertSee('JUARA 1')->assertSee('29 Oktober 2026')->assertDontSee('Tanda tangan')->assertDontSee('Stempel');
    }
}
