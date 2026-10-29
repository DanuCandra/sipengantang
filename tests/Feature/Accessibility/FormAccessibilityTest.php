<?php

namespace Tests\Feature\Accessibility;

use App\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;

class FormAccessibilityTest extends TestCase
{
    use LazilyRefreshDatabase;

    public function test_validation_error_is_associated_with_its_field_for_assistive_technology(): void
    {
        $this->withViewErrors(['email' => ['Email atau kata sandi tidak sesuai.']])->view('auth.login')
            ->assertSee('aria-invalid="true"', false)->assertSee('aria-describedby="email-error"', false)->assertSee('id="email-error"', false)->assertSee('Email atau kata sandi tidak sesuai.');
    }

    public function test_dashboard_exposes_labeled_navigation_and_skip_target_without_unfinished_module_promises(): void
    {
        $this->actingAs(User::factory()->admin()->create())->get(route('admin.dashboard'))
            ->assertSee('aria-controls="dashboard-navigation"', false)->assertSee('id="main-content"', false)
            ->assertSee('Tutup navigasi')->assertSee('Buka daftar event')->assertDontSee('Tahap 2 sedang aktif')->assertDontSee('Segera');
    }

    public function test_custom_input_ids_and_help_text_stay_linked_to_their_validation_error(): void
    {
        $this->withViewErrors(['email' => ['Periksa alamat email.']])
            ->blade('<x-form.field name="email" id="new-admin-email" label="Email Admin" aria-describedby="email-help" />')
            ->assertSee("for='new-admin-email'", false)->assertSee("id='new-admin-email'", false)
            ->assertSee('aria-describedby="email-help new-admin-email-error"', false)->assertSee('id="new-admin-email-error"', false);
    }
}
