<?php

namespace Tests\Feature\Judging;

use App\Enums\ActivityAction;
use App\Enums\CompetitionClassStatus;
use App\Enums\EvaluationStatus;
use App\Enums\EventStatus;
use App\Enums\RegistrationStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Event;
use App\Models\EventAdmin;
use App\Models\JudgeAssignment;
use App\Models\JudgeEvaluation;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\CreatesJudgingFixtures;
use Tests\TestCase;

class JudgeAssignmentTest extends TestCase
{
    use CreatesJudgingFixtures, LazilyRefreshDatabase;

    public function test_assigned_admin_can_assign_active_judge_without_duplicate_assignment_or_audit(): void
    {
        [, $event, $competitionClass] = $this->judgingFixture();
        $admin = $this->eventAdmin($event);
        $judge = User::factory()->judge()->create();

        $this->actingAs($admin)->post(route('admin.events.classes.judges.store', [$event, $competitionClass]), ['email' => $judge->email])->assertRedirect();
        $this->actingAs($admin)->post(route('admin.events.classes.judges.store', [$event, $competitionClass]), ['email' => $judge->email])->assertRedirect();

        $this->assertSame(1, JudgeAssignment::query()->where('competition_class_id', $competitionClass->id)->where('judge_id', $judge->id)->count());
        $this->assertSame(1, ActivityLog::query()->where('action', ActivityAction::JudgeAssigned)->count());
        $this->actingAs($admin)->get(route('admin.events.classes.judges.index', [$event, $competitionClass]))->assertSee($judge->email);
    }

    public function test_admin_can_create_judge_account_and_assign_it_transactionally(): void
    {
        [, $event, $competitionClass] = $this->judgingFixture();
        $admin = $this->eventAdmin($event);

        $this->actingAs($admin)->post(route('admin.events.classes.judges.accounts', [$event, $competitionClass]), [
            'name' => 'Juri Baru', 'email' => 'newjudge@example.test', 'password' => 'judge-password', 'password_confirmation' => 'judge-password', 'status' => 'INACTIVE',
        ])->assertRedirect(route('admin.events.classes.judges.index', [$event, $competitionClass]));

        $judge = User::query()->with('roles')->where('email', 'newjudge@example.test')->firstOrFail();
        $this->assertTrue($judge->hasRole(UserRole::Judge));
        $this->assertTrue($judge->isActive());
        $this->assertTrue(Hash::check('judge-password', $judge->password));
        $this->assertDatabaseHas('judge_assignments', ['judge_id' => $judge->id, 'competition_class_id' => $competitionClass->id]);
        $this->actingAs($admin)->get(route('admin.events.classes.judges.create', [$event, $competitionClass]))->assertSee('Buat akun Juri');
    }

    public function test_inactive_accounts_cannot_be_assigned(): void
    {
        [, $event, $competitionClass] = $this->judgingFixture();
        $admin = $this->eventAdmin($event);

        foreach ([User::factory()->inactive()->judge()->create(), User::factory()->inactive()->participant()->create()] as $candidate) {
            $this->actingAs($admin)->post(route('admin.events.classes.judges.store', [$event, $competitionClass]), ['email' => $candidate->email])->assertSessionHasErrors('email');
        }

        $this->assertDatabaseCount('judge_assignments', 1);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_existing_participant_account_can_be_assigned_as_judge_without_losing_original_role(): void
    {
        [, $event, $competitionClass] = $this->judgingFixture();
        $admin = $this->eventAdmin($event);
        $participant = User::factory()->participant()->create();
        $userCount = User::query()->count();

        $this->actingAs($admin)->post(route('admin.events.classes.judges.store', [$event, $competitionClass]), ['email' => $participant->email])->assertRedirect();

        $participant->load('roles');
        $this->assertTrue($participant->hasRole(UserRole::Participant));
        $this->assertTrue($participant->hasRole(UserRole::Judge));
        $this->assertSame($userCount, User::query()->count());
        $this->assertDatabaseHas('judge_assignments', ['judge_id' => $participant->id, 'competition_class_id' => $competitionClass->id]);
    }

    public function test_unassigned_admin_and_cross_event_class_cannot_be_managed(): void
    {
        [$judge, $event, $competitionClass] = $this->judgingFixture();
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('admin.events.classes.judges.index', [$event, $competitionClass]))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.events.classes.judges.store', [$event, $competitionClass]), ['email' => $judge->email])->assertForbidden();

        $otherEvent = Event::factory()->create();
        EventAdmin::factory()->for($otherEvent)->for($admin, 'user')->create();
        $this->actingAs($admin)->get(route('admin.events.classes.judges.index', [$otherEvent, $competitionClass]))->assertNotFound();
        $this->actingAs($admin)->post(route('admin.events.classes.judges.store', [$otherEvent, $competitionClass]), ['email' => $judge->email])->assertForbidden();
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_finalized_class_rejects_new_assignment_and_account_without_side_effects(): void
    {
        [$judge, $event, $competitionClass] = $this->judgingFixture();
        $admin = $this->eventAdmin($event);
        $competitionClass->update(['status' => CompetitionClassStatus::Finalized]);

        $this->actingAs($admin)->post(route('admin.events.classes.judges.store', [$event, $competitionClass]), ['email' => $judge->email])->assertSessionHasErrors('judging');
        $this->actingAs($admin)->post(route('admin.events.classes.judges.accounts', [$event, $competitionClass]), [
            'name' => 'Blocked', 'email' => 'blocked@example.test', 'password' => 'judge-password', 'password_confirmation' => 'judge-password',
        ])->assertSessionHasErrors('judging');

        $this->assertDatabaseMissing('users', ['email' => 'blocked@example.test']);
        $this->assertDatabaseCount('judge_assignments', 1);
    }

    public function test_explicit_start_opens_class_and_event_without_auto_finalization(): void
    {
        [, $event, $competitionClass] = $this->judgingFixture();
        $admin = $this->eventAdmin($event);
        $competitionClass->update(['status' => CompetitionClassStatus::Upcoming]);
        $event->update(['status' => EventStatus::Upcoming]);

        $this->actingAs($admin)->post(route('admin.events.classes.judging.start', [$event, $competitionClass]))->assertRedirect();

        $this->assertSame(CompetitionClassStatus::Ongoing, $competitionClass->refresh()->status);
        $this->assertSame(EventStatus::Ongoing, $event->refresh()->status);
        $this->assertDatabaseHas('activity_logs', ['action' => ActivityAction::JudgingStarted->value]);
        $this->actingAs($admin)->post(route('admin.events.classes.judging.start', [$event, $competitionClass]))->assertSessionHasErrors('judging');
        $this->assertSame(1, ActivityLog::query()->where('action', ActivityAction::JudgingStarted)->count());
    }

    public function test_start_requires_published_event_and_event_assignment(): void
    {
        [, $event, $competitionClass] = $this->judgingFixture();
        $competitionClass->update(['status' => CompetitionClassStatus::Upcoming]);
        $unrelated = User::factory()->admin()->create();
        $this->actingAs($unrelated)->post(route('admin.events.classes.judging.start', [$event, $competitionClass]))->assertForbidden();
        $admin = $this->eventAdmin($event);
        $event->update(['status' => EventStatus::Draft, 'published_at' => null]);

        $this->actingAs($admin)->post(route('admin.events.classes.judging.start', [$event, $competitionClass]))->assertSessionHasErrors('judging');

        $this->assertSame(CompetitionClassStatus::Upcoming, $competitionClass->refresh()->status);
        $this->assertDatabaseCount('activity_logs', 0);
    }

    public function test_progress_counts_submitted_eligible_evaluations_and_excludes_draft_and_dq(): void
    {
        [$judge, $event, $competitionClass, $registration] = $this->judgingFixture();
        $admin = $this->eventAdmin($event);
        $otherJudge = User::factory()->judge()->create();
        JudgeEvaluation::factory()->submitted()->for($registration)->for($judge, 'judge')->create();
        JudgeEvaluation::factory()->for($registration)->for($otherJudge, 'judge')->create();
        $dq = Registration::factory()->for($competitionClass)->create(['status' => RegistrationStatus::Disqualified]);
        JudgeEvaluation::factory()->submitted()->for($dq)->for($judge, 'judge')->create();

        $response = $this->actingAs($admin)->get(route('admin.events.classes.judges.index', [$event, $competitionClass]));

        $response->assertSee('Juri & progres penilaian', false);
        $progress = $response->viewData('progress');
        $this->assertSame(1, $progress['eligible']);
        $this->assertSame(2, $progress['assigned']);
        $this->assertSame(2, $progress['expected']);
        $this->assertSame(1, $progress['submitted']);
        $this->assertSame(1, $progress['drafts']);
        $this->assertSame(50, $progress['percent']);
        $this->assertSame(2, JudgeEvaluation::query()->submitted()->count());
        $this->assertSame(EvaluationStatus::Draft, JudgeEvaluation::query()->where('judge_id', $otherJudge->id)->sole()->status);
    }

    private function eventAdmin(Event $event): User
    {
        $admin = User::factory()->admin()->create();
        EventAdmin::factory()->for($event)->for($admin, 'user')->create();

        return $admin;
    }
}
