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

    private function createAgent(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_AGENT,
            'email_verified_at' => now(),
            'is_blocked' => false,
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

    public function test_agent_user_cannot_access_admin_panel(): void
    {
        $agent = $this->createAgent();

        $response = $this->actingAs($agent)->get('/admin');

        $response->assertForbidden();
    }

    // ---------------------------------------------------------------
    // canAccessPanel logic
    // ---------------------------------------------------------------

    public function test_admin_can_access_admin_panel_via_canAccessPanel(): void
    {
        $admin = $this->createAdmin();
        $panel = app(\Filament\Panel::class)::make()->id('admin');

        $this->assertTrue($admin->canAccessPanel($panel));
    }

    public function test_provider_cannot_access_admin_panel_via_canAccessPanel(): void
    {
        $provider = $this->createProvider();
        $panel = app(\Filament\Panel::class)::make()->id('admin');

        $this->assertFalse($provider->canAccessPanel($panel));
    }

    public function test_agent_cannot_access_admin_panel_via_canAccessPanel(): void
    {
        $agent = $this->createAgent();
        $panel = app(\Filament\Panel::class)::make()->id('admin');

        $this->assertFalse($agent->canAccessPanel($panel));
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

    // ---------------------------------------------------------------
    // Agent model – block / unblock / soft-delete / restore
    // (The Filament panel actions delegate directly to these model calls.)
    // ---------------------------------------------------------------

    public function test_agent_model_can_be_blocked(): void
    {
        $agent = $this->createAgent(['is_blocked' => false]);

        $agent->update(['is_blocked' => true]);

        $this->assertDatabaseHas('users', [
            'id' => $agent->id,
            'is_blocked' => true,
        ]);
    }

    public function test_agent_model_can_be_unblocked(): void
    {
        $agent = $this->createAgent(['is_blocked' => true]);

        $agent->update(['is_blocked' => false]);

        $this->assertDatabaseHas('users', [
            'id' => $agent->id,
            'is_blocked' => false,
        ]);
    }

    public function test_agent_model_can_be_soft_deleted(): void
    {
        $agent = $this->createAgent();

        $agent->delete();

        $this->assertSoftDeleted('users', ['id' => $agent->id]);
    }

    public function test_soft_deleted_agent_model_can_be_restored(): void
    {
        $agent = $this->createAgent();
        $agent->delete();

        $this->assertSoftDeleted('users', ['id' => $agent->id]);

        $agent->restore();

        $this->assertDatabaseHas('users', [
            'id' => $agent->id,
            'deleted_at' => null,
        ]);
    }

    // ---------------------------------------------------------------
    // Agent panel is inaccessible to admin (different guard / path)
    // ---------------------------------------------------------------

    public function test_admin_cannot_access_agent_panel_with_web_guard(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin)->get('/agent');

        $response->assertForbidden();
    }
}
