<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_authentication_routes_follow_documented_urls(): void
    {
        $this->assertSame(url('/login'), route('login'));
        $this->assertSame(url('/register'), route('register'));
        $this->assertSame(url('/forgot-password'), route('password.request'));
        $this->assertSame(url('/reset-password'), route('password.store'));
        $this->assertSame(url('/logout'), route('logout'));
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk()
            ->assertViewIs('auth.login')
            ->assertSee('Selamat datang kembali');
    }

    public function test_active_user_can_authenticate(): void
    {
        $user = User::factory()->participant()->create([
            'password' => 'secret-password',
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'secret-password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->participant()->create();

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_authenticate(): void
    {
        $user = User::factory()->inactive()->participant()->create([
            'password' => 'secret-password',
        ]);

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'secret-password',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_is_rate_limited_after_repeated_failures(): void
    {
        $user = User::factory()->participant()->create();

        foreach (range(1, 5) as $attempt) {
            $this->post(route('login'), [
                'email' => $user->email,
                'password' => 'wrong-password',
            ]);
        }

        $response = $this->post(route('login'), [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $response->assertSessionHasErrors('email');
        $message = session('errors')->first('email');

        $this->assertMatchesRegularExpression(
            '/^Terlalu banyak percobaan\. Coba lagi dalam \d+ detik\.$/',
            $message,
        );
        $this->assertGuest();
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->participant()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect(route('home'));
        $this->assertGuest();
    }
}
