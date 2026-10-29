<?php

namespace Tests\Feature\EventManagement;

use App\Enums\CriterionType;
use App\Models\CompetitionClass;
use App\Models\Event;
use App\Models\EventAdmin;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AdminClassManagementTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_assigned_admin_can_create_class_with_fixed_criteria_snapshot(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        EventAdmin::factory()->for($event)->for($admin, 'user')->create();

        $response = $this->actingAs($admin)->post(route('admin.events.classes.store', $event), $this->classPayload());

        $competitionClass = CompetitionClass::query()->with('criteria')->sole();
        $response->assertRedirect(route('admin.events.classes.show', [$event, $competitionClass]));
        $this->assertCount(5, $competitionClass->criteria);
        $this->assertSame(['C1', 'C2', 'C3', 'C4', 'C5'], $competitionClass->criteria->pluck('code')->all());
        $this->assertTrue($competitionClass->criteria->every(fn ($criterion): bool => $criterion->type === CriterionType::Benefit));
        $this->assertEqualsWithDelta(1.0, (float) $competitionClass->criteria->sum('weight'), 0.000001);
    }

    public function test_unassigned_admin_cannot_manage_event_or_create_class(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();

        $this->actingAs($admin)->get(route('admin.events.show', $event))->assertForbidden();
        $this->actingAs($admin)->post(route('admin.events.classes.store', $event), $this->classPayload())->assertForbidden();
    }

    public function test_nested_class_from_another_event_returns_not_found(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        $otherEvent = Event::factory()->create();
        EventAdmin::factory()->for($event)->for($admin, 'user')->create();
        $competitionClass = CompetitionClass::factory()->for($otherEvent)->create();

        $this->actingAs($admin)->get(route('admin.events.classes.show', [$event, $competitionClass]))->assertNotFound();
    }

    public function test_assigned_admin_can_render_class_detail(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        EventAdmin::factory()->for($event)->for($admin, 'user')->create();
        $competitionClass = CompetitionClass::factory()->for($event)->create();

        $this->actingAs($admin)->get(route('admin.events.classes.show', [$event, $competitionClass]))
            ->assertOk()
            ->assertSee($competitionClass->name)
            ->assertSee('Kriteria TOPSIS kelas');
    }

    public function test_cash_prize_requires_amount_and_is_scoped_to_class(): void
    {
        $admin = User::factory()->admin()->create();
        $event = Event::factory()->create();
        EventAdmin::factory()->for($event)->for($admin, 'user')->create();
        $competitionClass = CompetitionClass::factory()->for($event)->create();

        $route = route('admin.events.classes.prizes.store', [$event, $competitionClass]);
        $this->actingAs($admin)->post($route, ['rank' => 1, 'type' => 'CASH', 'title' => 'Juara 1'])->assertSessionHasErrors('cash_amount');
        $this->actingAs($admin)->post($route, ['rank' => 1, 'type' => 'CASH', 'title' => 'Juara 1', 'cash_amount' => 2500000])->assertRedirect();

        $this->assertDatabaseHas('prizes', ['competition_class_id' => $competitionClass->id, 'rank' => 1, 'cash_amount' => 2500000]);
    }

    /** @return array<string, int|string> */
    private function classPayload(): array
    {
        return [
            'name' => 'Murai Batu Utama',
            'bird_type' => 'Murai Batu',
            'description' => 'Kelas utama Murai Batu.',
            'registration_fee' => 150000,
            'quota' => 36,
            'start_at' => '2026-10-25 09:00:00',
            'end_at' => '2026-10-25 11:00:00',
        ];
    }
}
