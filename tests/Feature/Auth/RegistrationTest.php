<?php

namespace Tests\Feature\Auth;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk()
            ->assertViewIs('auth.register')
            ->assertSee('Buat akun peserta');
    }

    public function test_new_participant_can_register(): void
    {
        $response = $this->post(route('register'), [
            'name' => 'Peserta Baru',
            'email' => 'PESERTA@EXAMPLE.COM',
            'phone' => '08123456789',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
        ]);

        $response->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'peserta@example.com')->firstOrFail();

        $this->assertAuthenticatedAs($user);
        $this->assertTrue(Hash::check('secret-password', $user->password));
        $this->assertSame(UserStatus::Active, $user->status);
        $this->assertDatabaseHas('user_roles', [
            'user_id' => $user->id,
            'role' => UserRole::Participant->value,
        ]);
        $this->assertDatabaseHas('participant_profiles', [
            'user_id' => $user->id,
        ]);
    }

    public function test_registration_ignores_attempts_to_assign_privileged_role_or_status(): void
    {
        $this->post(route('register'), [
            'name' => 'Peserta Aman',
            'email' => 'aman@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'role' => UserRole::Owner->value,
            'status' => UserStatus::Suspended->value,
        ])->assertRedirect(route('dashboard'));

        $user = User::query()->where('email', 'aman@example.com')->firstOrFail();

        $this->assertSame(UserStatus::Active, $user->status);
        $this->assertTrue($user->hasRole(UserRole::Participant));
        $this->assertFalse($user->hasRole(UserRole::Owner));
    }

    public function test_registration_requires_unique_email_and_confirmed_password(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->post(route('register'), [
            'name' => 'Peserta Baru',
            'email' => 'taken@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'different-password',
        ]);

        $response->assertSessionHasErrors(['email', 'password']);
        $this->assertGuest();
    }

    public function test_authenticated_user_cannot_open_registration_screen(): void
    {
        $user = User::factory()->participant()->create();

        $response = $this->actingAs($user)->get(route('register'));

        $response->assertRedirect(route('dashboard'));
    }
}
