<?php

namespace Tests\Feature\Realtime;

use App\Domain\Ranking\Actions\FinalizeClassResultAction;
use App\Enums\EvaluationStatus;
use App\Enums\RegistrationStatus;
use App\Events\CertificateReady;
use App\Events\ClassStateChanged;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\Concerns\CreatesRankingFixtures;
use Tests\TestCase;

class RealtimeDeliveryTest extends TestCase
{
    use CreatesRankingFixtures, LazilyRefreshDatabase;

    public function test_broadcast_payload_is_explicit_minimal_and_unpublished_class_is_not_public(): void
    {
        $f = $this->rankingFixture();
        $event = new ClassStateChanged($f['class']->id);
        $this->assertSame(['class_id' => $f['class']->id], $event->broadcastWith());
        $names = array_map(fn ($channel) => $channel->name, $event->broadcastOn());
        $this->assertContains('class.'.$f['class']->id.'.public', $names);
        $this->assertContains('private-event.'.$f['event']->id.'.admin', $names);
        $f['event']->update(['published_at' => null]);
        $this->assertNotContains('class.'.$f['class']->id.'.public', array_map(fn ($channel) => $channel->name, $event->broadcastOn()));
        $ready = new CertificateReady($f['first']->participant_id, 99);
        $this->assertSame(['certificate_id' => 99], $ready->broadcastWith());
        $this->assertSame('private-user.'.$f['first']->participant_id, $ready->broadcastOn()[0]->name);
    }

    public function test_http_score_dq_and_finalization_work_when_reverb_is_unreachable_even_with_sync_queue_default(): void
    {
        $f = $this->rankingFixture();
        config(['broadcasting.default' => 'reverb', 'broadcasting.connections.reverb.options.host' => '127.0.0.1', 'broadcasting.connections.reverb.options.port' => 9, 'queue.default' => 'sync']);
        $evaluation = $f['first']->judgeEvaluations()->where('judge_id', $f['judges'][0]->id)->sole();
        $evaluation->update(['status' => EvaluationStatus::Draft, 'submitted_at' => null]);
        $this->actingAs($f['judges'][0])->post(route('judge.evaluations.submit', [$f['class'], $f['first']]), ['scores' => $this->scoresFor($f['criteria'], 8)])->assertSessionHasNoErrors();
        $this->post(route('judge.disqualifications.store', [$f['class'], $f['first']]), ['reason_code' => 'LATE_HANGING', 'note' => 'Terlambat.', 'confirm_disqualification' => 1])->assertSessionHasNoErrors();
        $this->assertSame(RegistrationStatus::Disqualified, $f['first']->fresh()->status);
        $this->actingAs($f['admin'])->post(route('admin.events.classes.ranking.finalize', [$f['event'], $f['class']]), ['confirmation' => 'FINALISASI'])->assertSessionHasNoErrors();
        $this->assertGreaterThan(0, DB::table('jobs')->count());
    }

    public function test_notification_is_dispatched_only_after_commit_and_rollback_does_not_queue_it(): void
    {
        $f = $this->rankingFixture();
        Event::fake([ClassStateChanged::class]);
        try {
            DB::transaction(function () use ($f): void {
                ClassStateChanged::dispatch($f['class']->id);
                Event::assertNotDispatched(ClassStateChanged::class);
                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException $exception) {
            $this->assertSame('rollback', $exception->getMessage());
        }
        Event::assertNotDispatched(ClassStateChanged::class);
    }

    public function test_ranking_fragment_is_authorized_and_contains_only_server_rendered_escaped_html(): void
    {
        $f = $this->rankingFixture();
        $name = '<script>alert("snapshot")</script>';
        $f['first']->bird()->first()->update(['name' => $name]);
        app(FinalizeClassResultAction::class)->execute($f['admin'], $f['class']);
        $url = route('admin.events.classes.ranking', [$f['event'], $f['class'], 'fragment' => 1]);
        $response = $this->actingAs($f['admin'])->getJson($url)->assertOk()->assertJsonStructure(['run_id', 'html']);
        $this->assertStringNotContainsString($name, $response->json('html'));
        $this->assertStringContainsString('&lt;script&gt;', $response->json('html'));
        $this->actingAs($f['judges'][0])->getJson($url)->assertForbidden();
    }
}
