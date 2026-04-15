<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function createAdmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
            'is_blocked' => false,
            'password' => Hash::make('AdminPass123!'),
        ], $overrides));
    }

    private function createProvider(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_PROVIDER,
            'email_verified_at' => now(),
        ], $overrides));
    }

    // ---------------------------------------------------------------
    // Access control
    // ---------------------------------------------------------------

    public function test_guest_is_redirected_from_admin_panel_to_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_user_can_access_admin_panel(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
    }

    public function test_provider_user_cannot_access_admin_panel(): void
    {
        $provider = $this->createProvider();

        $response = $this->actingAs($provider)->get('/admin');

        $response->assertForbidden();
    }

    // ---------------------------------------------------------------
    // canAccessPanel logic
    // ---------------------------------------------------------------

    public function test_admin_can_access_admin_panel_via_can_access_panel(): void
    {
        $admin = $this->createAdmin();
        $panel = app(\Filament\Panel::class)::make()->id('admin');

        $this->assertTrue($admin->canAccessPanel($panel));
    }

    public function test_provider_cannot_access_admin_panel_via_can_access_panel(): void
    {
        $provider = $this->createProvider();
        $panel = app(\Filament\Panel::class)::make()->id('admin');

        $this->assertFalse($provider->canAccessPanel($panel));
    }

    // ---------------------------------------------------------------
    // Admin panel resource pages
    // ---------------------------------------------------------------

    public function test_admin_can_view_providers_listing_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/admin/providers');

        $response->assertOk();
    }
}
