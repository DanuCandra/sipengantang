<?php

namespace Tests\Feature\Judging;

use App\Domain\Judging\Actions\RecordViolationAction;
use App\Domain\Judging\Services\JudgingProgressService;
use App\Enums\ActivityAction;
use App\Enums\CompetitionClassStatus;
use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Enums\ViolationAction;
use App\Enums\ViolationReasonCode;
use App\Events\ParticipantDisqualified;
use App\Events\WarningCreated;
use App\Models\ActivityLog;
use App\Models\JudgeAssignment;
use App\Models\JudgeEvaluation;
use App\Models\JudgeScore;
use App\Models\Registration;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\Concerns\CreatesJudgingFixtures;
use Tests\TestCase;

class ViolationWorkflowTest extends TestCase
{
    use CreatesJudgingFixtures, LazilyRefreshDatabase;

    public function test_first_warning_records_server_owned_identity_reason_and_audit_without_dq(): void
    {
        [$judge, , $class, $registration] = $this->judgingFixture();
        Event::fake([WarningCreated::class, ParticipantDisqualified::class]);

        $this->actingAs($judge)->post(route('judge.warnings.store', [$class, $registration]), [
            'reason_code' => 'SHOUTING', 'reason' => 'Forged reason', 'note' => 'Kejadian di sisi arena.',
            'action' => 'DISQUALIFICATION', 'judge_id' => 999999, 'registration_id' => 999999, 'status' => 'DISQUALIFIED',
        ])->assertRedirect(route('judge.evaluations.edit', [$class, $registration]));

        $violation = Violation::query()->sole();
        $this->assertSame(ViolationAction::Warning, $violation->action);
        $this->assertSame(ViolationReasonCode::Shouting, $violation->reason_code);
        $this->assertSame(ViolationReasonCode::Shouting->label(), $violation->reason);
        $this->assertSame($judge->id, $violation->judge_id);
        $this->assertSame($registration->id, $violation->registration_id);
        $this->assertSame(RegistrationStatus::Verified, $registration->fresh()->status);
        $this->assertNull($registration->fresh()->disqualified_at);
        $this->assertDatabaseHas('activity_logs', ['action' => ActivityAction::WarningCreated->value, 'subject_type' => 'violation', 'subject_id' => $violation->id, 'user_id' => $judge->id]);
        Event::assertDispatched(WarningCreated::class, fn ($event) => $event->violationId === $violation->id && $event->registrationId === $registration->id && $event->competitionClassId === $class->id);
        Event::assertNotDispatched(ParticipantDisqualified::class);
    }

    public function test_warning_by_another_assigned_judge_with_another_reason_escalates_to_dq(): void
    {
        [$judge, , $class, $registration] = $this->judgingFixture();
        $this->actingAs($judge)->post(route('judge.warnings.store', [$class, $registration]), ['reason_code' => 'SHOUTING'])->assertSessionHasNoErrors();
        $first = Violation::query()->sole()->toArray();
        $secondJudge = User::factory()->judge()->create();
        JudgeAssignment::factory()->for($class, 'competitionClass')->for($secondJudge, 'judge')->create();
        $this->travelTo(now()->startOfSecond());
        Event::fake([ParticipantDisqualified::class]);

        $this->actingAs($secondJudge)->post(route('judge.warnings.store', [$class, $registration]), [
            'reason_code' => 'PROVOCATION', 'note' => 'Mengulangi pelanggaran setelah diperingatkan.', 'confirm_disqualification' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseCount('violations', 2);
        $this->assertSame($first, Violation::query()->oldest('id')->first()->toArray());
        $last = Violation::query()->latest('id')->first();
        $this->assertSame(ViolationAction::Disqualification, $last->action);
        $this->assertSame($secondJudge->id, $last->judge_id);
        $this->assertSame(RegistrationStatus::Disqualified, $registration->fresh()->status);
        $this->assertTrue($registration->fresh()->disqualified_at->equalTo(now()));
        $log = ActivityLog::query()->where('action', ActivityAction::ParticipantDisqualified)->sole();
        $this->assertTrue($log->metadata['escalated']);
        Event::assertDispatched(ParticipantDisqualified::class, fn ($event) => $event->violationId === $last->id);
    }

    public function test_stale_warning_form_cannot_unintentionally_disqualify_or_duplicate_warning(): void
    {
        [$judge, , $class, $registration] = $this->judgingFixture();
        $url = route('judge.warnings.store', [$class, $registration]);
        $this->actingAs($judge)->post($url, ['reason_code' => 'DISTURBING'])->assertSessionHasNoErrors();

        $this->post($url, ['reason_code' => 'DISTURBING'])->assertSessionHasErrors(['note', 'confirm_disqualification']);
        $this->post($url, ['reason_code' => 'DISTURBING', 'note' => 'Kejadian kedua.'])->assertSessionHasErrors('confirm_disqualification');

        $this->assertDatabaseCount('violations', 1);
        $this->assertDatabaseCount('activity_logs', 1);
        $this->assertSame(RegistrationStatus::Verified, $registration->fresh()->status);
        $this->get(route('judge.evaluations.edit', [$class, $registration]))->assertSee('Peringatan sudah tercatat')->assertSee('Pelanggaran berulang')->assertSee('name="confirm_disqualification"', false);
    }

    #[DataProvider('directReasons')]
    public function test_direct_dq_records_reason_and_note_without_prior_warning(string $reasonCode, ?string $reason): void
    {
        [$judge, , $class, $registration] = $this->judgingFixture();

        $this->actingAs($judge)->post(route('judge.disqualifications.store', [$class, $registration]), [
            'reason_code' => $reasonCode, 'reason' => $reason, 'note' => 'Kejadian berat terkonfirmasi.', 'confirm_disqualification' => 1,
        ])->assertSessionHasNoErrors();

        $violation = Violation::query()->sole();
        $this->assertSame(ViolationAction::Disqualification, $violation->action);
        $this->assertSame($reasonCode, $violation->reason_code->value);
        $this->assertSame($reason ?? ViolationReasonCode::from($reasonCode)->label(), $violation->reason);
        $this->assertSame(RegistrationStatus::Disqualified, $registration->fresh()->status);
        $this->assertFalse(ActivityLog::query()->sole()->metadata['escalated']);
    }

    public static function directReasons(): array
    {
        return [['LATE_HANGING', null], ['NUMBER_MANIPULATION', null], ['OTHER', 'Pelanggaran berat lainnya.']];
    }

    #[DataProvider('invalidPayloads')]
    public function test_invalid_or_incompatible_payload_never_changes_registration(string $route, array $payload, array $errors): void
    {
        [$judge, , $class, $registration] = $this->judgingFixture();

        $this->actingAs($judge)->post(route($route, [$class, $registration]), $payload)->assertSessionHasErrors($errors);

        $this->assertDatabaseCount('violations', 0);
        $this->assertDatabaseCount('activity_logs', 0);
        $this->assertSame(RegistrationStatus::Verified, $registration->fresh()->status);
    }

    public static function invalidPayloads(): array
    {
        return [
            'missing reason' => ['judge.warnings.store', [], ['reason_code']],
            'unknown reason' => ['judge.warnings.store', ['reason_code' => 'UNKNOWN'], ['reason_code']],
            'wrong warning reason' => ['judge.warnings.store', ['reason_code' => 'LATE_HANGING'], ['reason_code']],
            'wrong direct reason' => ['judge.disqualifications.store', ['reason_code' => 'SHOUTING', 'note' => 'Detail', 'confirm_disqualification' => 1], ['reason_code']],
            'other explanation' => ['judge.warnings.store', ['reason_code' => 'OTHER'], ['reason']],
            'direct note and confirmation' => ['judge.disqualifications.store', ['reason_code' => 'LATE_HANGING'], ['note', 'confirm_disqualification']],
            'blank note' => ['judge.disqualifications.store', ['reason_code' => 'LATE_HANGING', 'note' => '   ', 'confirm_disqualification' => 1], ['note']],
            'unchecked confirmation' => ['judge.disqualifications.store', ['reason_code' => 'LATE_HANGING', 'note' => 'Detail', 'confirm_disqualification' => 0], ['confirm_disqualification']],
            'oversized note' => ['judge.warnings.store', ['reason_code' => 'SHOUTING', 'note' => str_repeat('a', 2001)], ['note']],
        ];
    }

    public function test_dq_preserves_submitted_and_draft_scores_blocks_scoring_and_excludes_progress(): void
    {
        [$judge, , $class, $registration, $criteria] = $this->judgingFixture();
        $secondJudge = User::factory()->judge()->create();
        JudgeAssignment::factory()->for($class, 'competitionClass')->for($secondJudge, 'judge')->create();
        $this->actingAs($judge)->post(route('judge.evaluations.submit', [$class, $registration]), ['scores' => $this->scoresFor($criteria, 9)])->assertSessionHasNoErrors();
        $this->actingAs($secondJudge)->post(route('judge.evaluations.draft', [$class, $registration]), ['scores' => [$criteria[0]->id => 7]])->assertSessionHasNoErrors();
        $evaluations = JudgeEvaluation::query()->orderBy('id')->get()->toArray();
        $scores = JudgeScore::query()->orderBy('id')->get()->toArray();
        $this->assertSame(1, app(JudgingProgressService::class)->summary($class)['eligible']);

        // Submitting scores does not remove this assigned judge's right to record violations.
        $this->actingAs($judge)->post(route('judge.disqualifications.store', [$class, $registration]), [
            'reason_code' => 'NUMBER_MANIPULATION', 'note' => 'Nomor peserta dimanipulasi.', 'confirm_disqualification' => 1,
        ])->assertSessionHasNoErrors();
        $this->actingAs($secondJudge)->post(route('judge.evaluations.submit', [$class, $registration]), ['scores' => $this->scoresFor($criteria)])->assertSessionHasErrors('judging');
        $this->assertSame($evaluations, JudgeEvaluation::query()->orderBy('id')->get()->toArray());
        $this->assertSame($scores, JudgeScore::query()->orderBy('id')->get()->toArray());
        $summary = app(JudgingProgressService::class)->summary($class);
        foreach (['eligible', 'expected', 'submitted', 'drafts'] as $key) {
            $this->assertSame(0, $summary[$key]);
        }
        $this->actingAs($judge)->get(route('judge.evaluations.edit', [$class, $registration]))->assertSee('Diskualifikasi.')->assertSee('Nilai terkunci')->assertDontSee('data-dialog-open', false);
    }

    public function test_repeated_dq_cannot_create_another_violation_or_change_timestamp(): void
    {
        [$judge, , $class, $registration] = $this->judgingFixture();
        $payload = ['reason_code' => 'LATE_HANGING', 'note' => 'Terlambat.', 'confirm_disqualification' => 1];
        $this->actingAs($judge)->post(route('judge.disqualifications.store', [$class, $registration]), $payload)->assertSessionHasNoErrors();
        $timestamp = $registration->fresh()->disqualified_at;
        $this->travel(1)->hours();

        $this->post(route('judge.disqualifications.store', [$class, $registration]), $payload)->assertSessionHasErrors('violation');
        $this->post(route('judge.warnings.store', [$class, $registration]), ['reason_code' => 'SHOUTING'])->assertSessionHasErrors('violation');

        $this->assertDatabaseCount('violations', 1);
        $this->assertDatabaseCount('activity_logs', 1);
        $this->assertTrue($registration->fresh()->disqualified_at->equalTo($timestamp));
    }

    #[DataProvider('closedClasses')]
    public function test_violations_cannot_change_closed_or_finalized_class(CompetitionClassStatus $status): void
    {
        [$judge, , $class, $registration] = $this->judgingFixture();
        $class->update(['status' => $status]);

        $this->actingAs($judge)->post(route('judge.warnings.store', [$class, $registration]), ['reason_code' => 'SHOUTING'])->assertSessionHasErrors('violation');

        $this->assertDatabaseCount('violations', 0);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public static function closedClasses(): array
    {
        return array_map(fn ($status) => [$status], [CompetitionClassStatus::Upcoming, CompetitionClassStatus::JudgingCompleted, CompetitionClassStatus::Finalized, CompetitionClassStatus::Cancelled]);
    }

    public function test_unverified_or_closed_event_cannot_receive_violation(): void
    {
        [$judge, $event, $class, $registration] = $this->judgingFixture();
        foreach ([RegistrationStatus::PendingPayment, RegistrationStatus::WaitingVerification, RegistrationStatus::PaymentRejected, RegistrationStatus::Cancelled] as $status) {
            $registration->update(['status' => $status]);
            $this->actingAs($judge)->post(route('judge.warnings.store', [$class, $registration]), ['reason_code' => 'SHOUTING'])->assertSessionHasErrors('violation');
        }
        $registration->update(['status' => RegistrationStatus::Verified]);
        $event->update(['status' => EventStatus::Completed]);
        $this->post(route('judge.warnings.store', [$class, $registration]), ['reason_code' => 'SHOUTING'])->assertSessionHasErrors('violation');
        $event->update(['status' => EventStatus::Ongoing, 'published_at' => null]);
        $this->post(route('judge.warnings.store', [$class, $registration]), ['reason_code' => 'SHOUTING'])->assertSessionHasErrors('violation');
        $this->assertDatabaseCount('violations', 0);
    }

    public function test_warning_is_scoped_to_registration_not_bird_across_classes(): void
    {
        [$judge, , $class, $registration] = $this->judgingFixture();
        $other = Registration::factory()->create(['bird_id' => $registration->bird_id, 'participant_id' => $registration->participant_id, 'status' => RegistrationStatus::Verified]);
        Violation::factory()->for($other)->create();

        $this->actingAs($judge)->post(route('judge.warnings.store', [$class, $registration]), ['reason_code' => 'OTHER', 'reason' => 'Gangguan ringan lainnya.'])->assertSessionHasNoErrors();

        $this->assertSame(ViolationAction::Warning, $registration->violations()->sole()->action);
        $this->assertSame(RegistrationStatus::Verified, $registration->fresh()->status);
    }

    public function test_rollback_restores_violation_status_audit_and_defers_event_until_commit(): void
    {
        [$judge, , , $registration] = $this->judgingFixture();
        Event::fake([ParticipantDisqualified::class]);
        try {
            DB::transaction(function () use ($judge, $registration): void {
                app(RecordViolationAction::class)->execute($judge, $registration, ViolationAction::Disqualification, ViolationReasonCode::LateHanging, null, 'Terlambat.', true);
                Event::assertNotDispatched(ParticipantDisqualified::class);
                throw new RuntimeException('rollback');
            });
        } catch (RuntimeException $exception) {
            $this->assertSame('rollback', $exception->getMessage());
        }
        $this->assertDatabaseCount('violations', 0);
        $this->assertDatabaseCount('activity_logs', 0);
        $this->assertSame(RegistrationStatus::Verified, $registration->fresh()->status);
        $this->assertNull($registration->fresh()->disqualified_at);
        Event::assertNotDispatched(ParticipantDisqualified::class);
    }
}
