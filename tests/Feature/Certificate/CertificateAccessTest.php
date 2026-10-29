<?php

namespace Tests\Feature\Certificate;

use App\Domain\Certificate\Actions\GenerateCertificateAction;
use App\Domain\Ranking\Actions\FinalizeClassResultAction;
use App\Domain\Ranking\Actions\RecalculateProvisionalRankingAction;
use App\Events\ResultFinalized;
use App\Jobs\GenerateWinnerCertificates;
use App\Models\Prize;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\CreatesRankingFixtures;
use Tests\TestCase;

class CertificateAccessTest extends TestCase
{
    use CreatesRankingFixtures, LazilyRefreshDatabase;

    private function certificateFixture(): array
    {
        $f = $this->rankingFixture();
        Prize::factory()->for($f['class'], 'competitionClass')->create(['rank' => 1]);
        $run = app(FinalizeClassResultAction::class)->execute($f['admin'], $f['class']);
        Storage::fake('private');
        $f['certificate'] = app(GenerateCertificateAction::class)->execute($run->results()->where('rank', 1)->sole());

        return $f;
    }

    public function test_own_participant_can_preview_download_and_list_private_pdf(): void
    {
        $f = $this->certificateFixture();
        $certificate = $f['certificate'];
        $this->actingAs($f['first']->participant)->get(route('participant.certificates.index'))->assertOk()->assertSee($certificate->metadata_snapshot['bird_name'])->assertSee('Unduh PDF');
        $response = $this->get(route('certificates.pdf', $certificate));
        $response->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->streamedContent());
        $this->assertStringStartsWith('inline;', $response->headers->get('Content-Disposition'));
        $this->assertTrue($response->headers->hasCacheControlDirective('no-store'));
        $this->get(route('certificates.pdf', [$certificate, 'download' => 1]))->assertDownload('piagam-'.$certificate->metadata_snapshot['number'].'.pdf');
        $this->get(route('participant.certificates.live', ['results' => [$certificate->ranking_result_id]]))->assertJsonPath('certificates.0.ready', true)->assertDontSee($certificate->file_path);
    }

    public function test_guest_other_participant_unrelated_admin_and_judge_cannot_get_certificate(): void
    {
        $f = $this->certificateFixture();
        $this->get(route('certificates.pdf', $f['certificate']))->assertRedirect(route('login'));
        foreach ([User::factory()->participant()->create(), User::factory()->admin()->create(), $f['judges'][0]] as $user) {
            $this->actingAs($user)->get(route('certificates.pdf', $f['certificate']))->assertForbidden();
            $this->assertFalse(Gate::forUser($user)->allows('view', $f['certificate']));
        }
        foreach ([$f['admin'], $f['event']->owner] as $user) {
            $this->assertTrue(Gate::forUser($user)->allows('view', $f['certificate']));
        }
        $this->actingAs($f['second']->participant)->get(route('participant.certificates.index'))->assertDontSee($f['first']->bird->name);
        $this->get(route('participant.certificates.live', ['results' => [$f['certificate']->ranking_result_id]]))->assertExactJson(['certificates' => []]);
    }

    public function test_missing_pdf_shows_pending_and_download_has_clear_unavailable_response(): void
    {
        Event::fake([ResultFinalized::class]);
        $f = $this->certificateFixture();
        Storage::disk('private')->delete($f['certificate']->file_path);
        $this->actingAs($f['first']->participant)->get(route('participant.certificates.index'))->assertSee('Piagam menunggu pembuatan');
        $this->get(route('certificates.pdf', $f['certificate']))->assertStatus(503);
        Queue::fake([GenerateWinnerCertificates::class]);
        $this->actingAs($f['admin'])->post(route('admin.events.classes.certificates.generate', [$f['event'], $f['class']]))->assertSessionHasNoErrors();
        Queue::assertPushed(GenerateWinnerCertificates::class);
        $this->actingAs(User::factory()->admin()->create())->post(route('admin.events.classes.certificates.generate', [$f['event'], $f['class']]))->assertForbidden();
    }

    public function test_certificate_access_rejects_non_final_and_mismatched_source_records(): void
    {
        $f = $this->certificateFixture();
        $certificate = $f['certificate'];
        foreach (['registration_id' => $f['second']->id, 'rank' => 2, 'competition_class_id' => 999999, 'event_id' => 999999] as $field => $value) {
            $invalid = clone $certificate;
            $invalid->setAttribute($field, $value);
            $this->assertFalse(Gate::forUser($f['event']->owner)->allows('view', $invalid));
        }
        $other = $this->rankingFixture();
        $run = app(RecalculateProvisionalRankingAction::class)->execute($other['class']);
        $invalid = clone $certificate;
        $invalid->ranking_result_id = $run->results()->firstOrFail()->id;
        $this->assertFalse(Gate::forUser($f['first']->participant)->allows('view', $invalid));
    }
}
