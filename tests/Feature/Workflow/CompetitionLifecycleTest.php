<?php

namespace Tests\Feature\Workflow;

use App\Domain\Certificate\Actions\GenerateCertificateAction;
use App\Domain\Certificate\Services\CertificateEligibility;
use App\Enums\CompetitionClassStatus;
use App\Enums\EventStatus;
use App\Jobs\GenerateWinnerCertificates;
use App\Models\Bird;
use App\Models\Certificate;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\RankingRun;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompetitionLifecycleTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_http_competition_lifecycle_reaches_private_winner_certificate_and_public_immutable_result(): void
    {
        Storage::fake('public');
        Storage::fake('private');
        Queue::fake([GenerateWinnerCertificates::class]);
        $owner = User::factory()->owner()->create();
        $admin = User::factory()->admin()->create();
        $judges = User::factory()->judge()->count(3)->create();
        $participants = User::factory()->participant()->count(2)->create();

        $this->actingAs($owner)->post(route('owner.events.store'), ['name' => 'Kompetisi QA Menyeluruh', 'location_name' => 'Arena QA', 'address' => 'Alamat event publik', 'start_at' => now()->addDay()->toDateTimeString()])->assertSessionHasNoErrors();
        $event = Event::query()->sole();
        $this->post(route('owner.events.admins.store', $event), ['email' => $admin->email])->assertSessionHasNoErrors();
        $this->actingAs($admin)->post(route('admin.events.classes.store', $event), ['name' => 'Murai Utama', 'bird_type' => 'Murai Batu', 'registration_fee' => 150000, 'quota' => 2, 'start_at' => now()->addDay()->toDateTimeString()])->assertSessionHasNoErrors();
        $class = CompetitionClass::query()->with('criteria')->sole();
        $this->assertSame(5, $class->criteria->count());
        $this->post(route('admin.events.classes.prizes.store', [$event, $class]), ['rank' => 1, 'type' => 'CASH', 'title' => 'Juara Utama', 'cash_amount' => 1000000])->assertSessionHasNoErrors();
        foreach ($judges as $judge) {
            $this->post(route('admin.events.classes.judges.store', [$event, $class]), ['email' => $judge->email])->assertSessionHasNoErrors();
        }
        $this->actingAs($owner)->post(route('owner.events.publish', $event))->assertSessionHasNoErrors();
        $this->assertSame(EventStatus::Upcoming, $event->fresh()->status);

        $registrations = [];
        foreach ($participants as $index => $participant) {
            $this->actingAs($participant)->post(route('participant.birds.store'), ['name' => $index === 0 ? 'Panglima QA' : 'Rajawali QA', 'bird_type' => 'Murai Batu', 'photo' => UploadedFile::fake()->image('bird.jpg')])->assertSessionHasNoErrors();
            $bird = Bird::query()->where('owner_id', $participant->id)->sole();
            $this->post(route('participant.registrations.store'), ['competition_class_id' => $class->id, 'bird_id' => $bird->id, 'gantangan_number' => (string) ($index + 1)])->assertSessionHasNoErrors();
            $registration = Registration::query()->where('participant_id', $participant->id)->sole();
            $registrations[] = $registration;
            $this->get(route('participant.registrations.proof', $registration))->assertForbidden();
            $this->post(route('participant.registrations.payments.store', $registration), ['amount' => 150000, 'proof' => UploadedFile::fake()->image('proof.jpg')])->assertSessionHasNoErrors();
            $payment = $registration->paymentSubmissions()->sole();
            $this->actingAs($admin)->patch(route('admin.events.payments.update', [$event, $payment]), ['decision' => 'VERIFIED'])->assertSessionHasNoErrors();
            $this->actingAs($participant)->get(route('participant.registrations.proof', $registration))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        }

        $this->actingAs($admin)->post(route('admin.events.classes.judging.start', [$event, $class]))->assertSessionHasNoErrors();
        foreach ($judges as $judge) {
            foreach ($registrations as $index => $registration) {
                $scores = $class->criteria->mapWithKeys(fn ($criterion) => [$criterion->id => $index === 0 ? 9 : 7])->all();
                $this->actingAs($judge)->post(route('judge.evaluations.submit', [$class, $registration]), ['scores' => $scores])->assertSessionHasNoErrors();
            }
        }
        $this->getJson(route('events.classes.live', [$event, $class]))->assertJsonPath('percent', 100)->assertJsonPath('result_url', null)->assertDontSee('preference_value');
        $this->get(route('events.classes.results', [$event, $class]))->assertNotFound();
        $this->actingAs($admin)->post(route('admin.events.classes.ranking.finalize', [$event, $class]), ['confirmation' => 'FINALISASI'])->assertSessionHasNoErrors();
        $run = RankingRun::query()->where('type', 'FINAL')->sole();
        $this->assertSame(CompetitionClassStatus::Finalized, $class->fresh()->status);
        $this->assertSame($registrations[0]->id, $run->results()->where('rank', 1)->sole()->registration_id);
        Queue::assertPushed(GenerateWinnerCertificates::class, fn ($job) => $job->rankingRunId === $run->id);
        (new GenerateWinnerCertificates($run->id))->handle(app(CertificateEligibility::class), app(GenerateCertificateAction::class));
        $certificate = Certificate::query()->sole();

        $this->actingAs($participants[0])->get(route('participant.certificates.index'))->assertSee('Unduh PDF');
        $this->get(route('certificates.pdf', $certificate))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->actingAs($participants[1])->get(route('certificates.pdf', $certificate))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.events.complete', $event), ['confirmation' => 'SELESAI'])->assertSessionHasNoErrors();
        $this->assertSame(EventStatus::Completed, $event->fresh()->status);
        $this->post(route('admin.events.classes.ranking.finalize', [$event, $class]), ['confirmation' => 'FINALISASI'])->assertSessionHasErrors('ranking');
        $this->assertSame(1, RankingRun::query()->where('type', 'FINAL')->count());

        $this->post(route('logout'))->assertRedirect();
        $this->get(route('events.classes.results', [$event, $class]))->assertSee('Panglima QA')->assertSee('Juara Utama')->assertDontSee($participants[0]->email)->assertDontSee($certificate->file_path);
    }
}
