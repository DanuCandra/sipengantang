<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    use LazilyRefreshDatabase;

    /**
     * @return array<string, array{string, string, string}>
     */
    public static function roleDashboardProvider(): array
    {
        return [
            'owner' => ['owner', 'owner.dashboard', '/owner/dashboard'],
            'admin' => ['admin', 'admin.dashboard', '/admin/dashboard'],
            'judge' => ['judge', 'judge.dashboard', '/judge/dashboard'],
            'participant' => ['participant', 'participant.dashboard', '/participant/dashboard'],
        ];
    }

    #[DataProvider('roleDashboardProvider')]
    public function test_user_is_redirected_to_own_role_dashboard(
        string $factoryState,
        string $routeName,
        string $routePath,
    ): void {
        $user = User::factory()->{$factoryState}()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $this->assertSame(url($routePath), route($routeName));
        $response->assertRedirect(route($routeName));
        $this->actingAs($user)
            ->get(route($routeName))
            ->assertOk()
            ->assertViewIs('dashboard.index')
            ->assertViewHas('roles');
    }

    public function test_multi_role_redirect_uses_documented_priority(): void
    {
        $user = User::factory()->participant()->admin()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_user_cannot_open_another_roles_dashboard(): void
    {
        $participant = User::factory()->participant()->create();

        $response = $this->actingAs($participant)->get(route('owner.dashboard'));

        $response->assertForbidden();
    }

    public function test_user_without_role_cannot_open_dashboard(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertForbidden();
    }

    public function test_inactive_authenticated_user_is_logged_out(): void
    {
        $user = User::factory()->inactive()->participant()->create();

        $response = $this->actingAs($user)->get(route('participant.dashboard'));

        $response->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_guest_is_redirected_to_login_from_protected_area(): void
    {
        $response = $this->get(route('owner.dashboard'));

        $response->assertRedirect(route('login'));
    }

    public function test_landing_page_renders_gantangscore_identity(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk()
            ->assertSee('GantangScore')
            ->assertSee('Penilaian lomba');
    }
}
