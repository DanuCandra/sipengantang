<?php

namespace Tests\Feature\Judging;

use App\Models\EventAdmin;
use App\Models\JudgeAssignment;
use App\Models\User;
use App\Models\Violation;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\Concerns\CreatesJudgingFixtures;
use Tests\TestCase;

class ViolationAccessTest extends TestCase
{
    use CreatesJudgingFixtures, LazilyRefreshDatabase;

    public function test_guest_unassigned_judge_admin_and_participant_cannot_issue_violations(): void
    {
        [, , $class, $registration] = $this->judgingFixture();
        $this->post(route('judge.warnings.store', [$class, $registration]))->assertRedirect(route('login'));
        foreach ([User::factory()->judge()->create(), User::factory()->admin()->create(), $registration->participant] as $user) {
            $this->actingAs($user)->post(route('judge.warnings.store', [$class, $registration]), ['reason_code' => 'SHOUTING'])->assertForbidden();
            $this->post(route('judge.disqualifications.store', [$class, $registration]), ['reason_code' => 'LATE_HANGING', 'note' => 'Detail', 'confirm_disqualification' => 1])->assertForbidden();
        }
        $this->assertDatabaseCount('violations', 0);
    }

    public function test_cross_class_resource_is_forbidden_even_for_judge_assigned_to_both(): void
    {
        [$judge, , $class] = $this->judgingFixture();
        [, , $otherClass, $otherRegistration] = $this->judgingFixture();
        JudgeAssignment::factory()->for($otherClass, 'competitionClass')->for($judge, 'judge')->create();

        $this->actingAs($judge)->post(route('judge.warnings.store', [$class, $otherRegistration]), ['reason_code' => 'SHOUTING'])->assertForbidden();
        $this->post(route('judge.disqualifications.store', [$class, $otherRegistration]), ['reason_code' => 'LATE_HANGING', 'note' => 'Detail', 'confirm_disqualification' => 1])->assertForbidden();

        $this->assertDatabaseCount('violations', 0);
    }

    public function test_internal_history_policy_requires_event_or_class_assignment_not_global_role(): void
    {
        [$judge, $event, , $registration] = $this->judgingFixture();
        $violation = Violation::factory()->for($registration)->for($judge, 'judge')->create();
        $admin = User::factory()->admin()->create();
        EventAdmin::factory()->for($event)->create(['user_id' => $admin->id]);

        foreach ([$judge, $event->owner, $admin] as $user) {
            $this->assertTrue(Gate::forUser($user)->allows('view', $violation));
            $this->assertTrue(Gate::forUser($user)->allows('viewAny', [Violation::class, $registration]));
        }
        foreach ([$registration->participant, User::factory()->judge()->create(), User::factory()->admin()->create(), User::factory()->owner()->create()] as $user) {
            $this->assertFalse(Gate::forUser($user)->allows('view', $violation));
            $this->assertFalse(Gate::forUser($user)->allows('viewAny', [Violation::class, $registration]));
        }
        $this->assertFalse(Gate::forUser($admin)->allows('create', [Violation::class, $registration]));
    }

    public function test_admin_history_is_event_scoped_paginated_and_escapes_internal_notes(): void
    {
        [$judge, $event, , $registration] = $this->judgingFixture();
        $admin = User::factory()->admin()->create();
        EventAdmin::factory()->for($event)->create(['user_id' => $admin->id]);
        $note = '<script>alert("internal")</script>';
        $reason = '<img src=x onerror=alert("reason")>';
        Violation::factory()->for($registration)->for($judge, 'judge')->create(['reason_code' => 'OTHER', 'reason' => $reason, 'note' => $note]);
        $other = Violation::factory()->create(['note' => 'SECRET-OTHER-EVENT']);

        $response = $this->actingAs($admin)->get(route('admin.events.violations.index', $event));
        $response->assertOk()->assertSee($note)->assertDontSee($note, false)->assertDontSee('SECRET-OTHER-EVENT');
        $response->assertSee($reason)->assertDontSee($reason, false);
        $this->assertSame(1, $response->viewData('violations')->total());
        $this->get(route('admin.events.registrations.show', [$event, $registration]))->assertOk()->assertSee($note);
        $this->get(route('admin.events.violations.index', $other->registration->competitionClass->event))->assertForbidden();
        $this->get(route('admin.events.registrations.show', [$event, $other->registration]))->assertNotFound();
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.events.violations.index', $event))->assertForbidden();
    }

    public function test_assigned_judges_share_violation_history_but_participant_and_public_never_get_notes(): void
    {
        [$judge, $event, $class, $registration] = $this->judgingFixture();
        $secondJudge = User::factory()->judge()->create(['name' => 'JURI-PRIVATE-HISTORY']);
        JudgeAssignment::factory()->for($class, 'competitionClass')->for($secondJudge, 'judge')->create();
        Violation::factory()->for($registration)->for($secondJudge, 'judge')->create(['reason_code' => 'OTHER', 'reason' => 'INTERNAL-REASON-ONLY', 'note' => 'INTERNAL-NOTE-ONLY']);

        $this->actingAs($judge)->get(route('judge.evaluations.edit', [$class, $registration]))->assertOk()->assertSee('INTERNAL-NOTE-ONLY')->assertSee('JURI-PRIVATE-HISTORY');
        $this->get(route('judge.classes.show', $class))->assertSee('1 peringatan tercatat');
        $response = $this->actingAs($registration->participant)->get(route('participant.registrations.show', $registration));
        $response->assertOk()->assertSee('Peringatan tercatat.')->assertDontSee('INTERNAL-NOTE-ONLY')->assertDontSee('INTERNAL-REASON-ONLY')->assertDontSee('JURI-PRIVATE-HISTORY');
        $this->assertFalse($response->viewData('registration')->relationLoaded('violations'));
        $this->get(route('events.show', $event))->assertOk()->assertDontSee('INTERNAL-NOTE-ONLY')->assertDontSee('INTERNAL-REASON-ONLY');
    }

    public function test_disqualified_participant_only_sees_own_status_not_internal_dq_detail(): void
    {
        [$judge, , $class, $registration] = $this->judgingFixture();
        $this->actingAs($judge)->post(route('judge.disqualifications.store', [$class, $registration]), [
            'reason_code' => 'OTHER', 'reason' => 'INTERNAL-DQ-REASON', 'note' => 'INTERNAL-DQ-NOTE', 'confirm_disqualification' => 1,
        ])->assertSessionHasNoErrors();

        $this->actingAs($registration->participant)->get(route('participant.registrations.show', $registration))->assertOk()->assertSee('Diskualifikasi.')->assertDontSee('INTERNAL-DQ-REASON')->assertDontSee('INTERNAL-DQ-NOTE');
        $this->actingAs(User::factory()->participant()->create())->get(route('participant.registrations.show', $registration))->assertForbidden();
    }

    public function test_owner_can_monitor_only_owned_event_violation_history(): void
    {
        [$judge, $event, , $registration] = $this->judgingFixture();
        Violation::factory()->for($registration)->for($judge, 'judge')->create(['note' => 'OWNED-EVENT-NOTE']);
        Violation::factory()->create(['note' => 'OTHER-EVENT-PRIVATE']);

        $this->actingAs($event->owner)->get(route('owner.events.show', $event))->assertOk()->assertSee('OWNED-EVENT-NOTE')->assertDontSee('OTHER-EVENT-PRIVATE');
        $this->actingAs(User::factory()->owner()->create())->get(route('owner.events.show', $event))->assertForbidden();
    }

    public function test_invalid_dq_form_reopens_only_its_dialog_with_safe_old_input_and_errors(): void
    {
        [$judge, , $class, $registration] = $this->judgingFixture();
        $page = route('judge.evaluations.edit', [$class, $registration]);
        $this->actingAs($judge)->from($page)->post(route('judge.disqualifications.store', [$class, $registration]), [
            'violation_form' => 'disqualification', 'reason_code' => 'OTHER', 'reason' => '<script>old reason</script>',
        ])->assertRedirect($page)->assertSessionHasErrors(['note', 'confirm_disqualification'])->assertSessionHasInput('violation_form', 'disqualification');

        // Follow the redirect with the same session cookie, as a browser would.
        $response = $this->withCookie(config('session.cookie'), app('session')->getId())->get($page);
        $this->assertTrue($response->viewData('errors')->any());
        $response->assertOk()->assertSee('Pelanggaran belum tersimpan')->assertSee('Catatan kejadian wajib diisi')->assertSee('<script>old reason</script>')->assertDontSee('<script>old reason</script>', false);
        $this->assertSame(1, substr_count($response->getContent(), 'data-auto-open'));
        $this->assertMatchesRegularExpression('/id="violation-disqualification"[^>]*data-auto-open/', $response->getContent());
        $this->assertDatabaseCount('violations', 0);
    }
}
