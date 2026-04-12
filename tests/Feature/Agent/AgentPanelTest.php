<?php

namespace Tests\Feature\Agent;

use App\Filament\Agent\Pages\Auth\ForceChangePassword;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AgentPanelTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function createAgent(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_AGENT,
            'email_verified_at' => now(),
            'is_blocked' => false,
            'must_change_password' => false,
            'password' => Hash::make('AgentPass123!'),
        ], $overrides));
    }

    private function createAdmin(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'role' => User::ROLE_ADMIN,
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

    public function test_guest_is_redirected_from_agent_panel_to_login(): void
    {
        $response = $this->get('/agent');

        $response->assertRedirect('/agent/login');
    }

    public function test_verified_unblocked_agent_can_access_agent_panel(): void
    {
        $agent = $this->createAgent();

        $response = $this->actingAs($agent, 'agent')->get('/agent');

        $response->assertOk();
    }

    public function test_blocked_agent_cannot_access_agent_panel(): void
    {
        $agent = $this->createAgent(['is_blocked' => true]);

        $response = $this->actingAs($agent, 'agent')->get('/agent');

        $response->assertForbidden();
    }

    public function test_provider_cannot_access_agent_panel(): void
    {
        $provider = $this->createProvider();

        $response = $this->actingAs($provider, 'agent')->get('/agent');

        $response->assertForbidden();
    }

    public function test_admin_cannot_access_agent_panel(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'agent')->get('/agent');

        $response->assertForbidden();
    }

    // ---------------------------------------------------------------
    // canAccessPanel logic
    // ---------------------------------------------------------------

    public function test_unblocked_agent_can_access_agent_panel_via_canAccessPanel(): void
    {
        $agent = $this->createAgent(['is_blocked' => false]);
        $panel = app(\Filament\Panel::class)::make()->id('agent');

        $this->assertTrue($agent->canAccessPanel($panel));
    }

    public function test_blocked_agent_cannot_access_agent_panel_via_canAccessPanel(): void
    {
        $agent = $this->createAgent(['is_blocked' => true]);
        $panel = app(\Filament\Panel::class)::make()->id('agent');

        $this->assertFalse($agent->canAccessPanel($panel));
    }

    public function test_provider_cannot_access_agent_panel_via_canAccessPanel(): void
    {
        $provider = $this->createProvider();
        $panel = app(\Filament\Panel::class)::make()->id('agent');

        $this->assertFalse($provider->canAccessPanel($panel));
    }

    public function test_admin_cannot_access_agent_panel_via_canAccessPanel(): void
    {
        $admin = $this->createAdmin();
        $panel = app(\Filament\Panel::class)::make()->id('agent');

        $this->assertFalse($admin->canAccessPanel($panel));
    }

    // ---------------------------------------------------------------
    // Email verification enforcement
    // ---------------------------------------------------------------

    public function test_unverified_agent_is_redirected_to_email_verification_notice(): void
    {
        $agent = $this->createAgent(['email_verified_at' => null]);

        $response = $this->actingAs($agent, 'agent')->get('/agent');

        $response->assertRedirect(route('verification.notice'));
    }

    // ---------------------------------------------------------------
    // Force password change enforcement
    // ---------------------------------------------------------------

    public function test_agent_with_must_change_password_is_redirected_to_force_change_password(): void
    {
        $agent = $this->createAgent(['must_change_password' => true]);

        $forceChangeUrl = \App\Filament\Agent\Pages\Auth\ForceChangePassword::getUrl(panel: 'agent');

        $response = $this->actingAs($agent, 'agent')->get('/agent');

        $response->assertRedirect($forceChangeUrl);
    }

    public function test_agent_without_must_change_password_can_access_panel_normally(): void
    {
        $agent = $this->createAgent(['must_change_password' => false]);

        $response = $this->actingAs($agent, 'agent')->get('/agent');

        $response->assertOk();
    }

    public function test_force_change_password_page_is_accessible_when_flag_is_set(): void
    {
        $agent = $this->createAgent(['must_change_password' => true]);

        $forceChangeUrl = \App\Filament\Agent\Pages\Auth\ForceChangePassword::getUrl(panel: 'agent');

        $response = $this->actingAs($agent, 'agent')->get($forceChangeUrl);

        $response->assertOk();
    }

    public function test_force_change_password_page_redirects_away_when_flag_is_not_set(): void
    {
        $agent = $this->createAgent(['must_change_password' => false]);

        $forceChangeUrl = \App\Filament\Agent\Pages\Auth\ForceChangePassword::getUrl(panel: 'agent');

        $response = $this->actingAs($agent, 'agent')->get($forceChangeUrl);

        $response->assertRedirect('/agent');
    }

    // ---------------------------------------------------------------
    // Agent panel resource pages
    // ---------------------------------------------------------------

    public function test_agent_can_view_provider_listings_page(): void
    {
        $agent = $this->createAgent();

        $response = $this->actingAs($agent, 'agent')->get('/agent/provider-listings');

        $response->assertOk();
    }

    public function test_agent_can_access_create_provider_listing_page(): void
    {
        $agent = $this->createAgent();

        $response = $this->actingAs($agent, 'agent')->get('/agent/provider-listings/create');

        $response->assertOk();
    }

    // ---------------------------------------------------------------
    // Force password change – successful submission
    // ---------------------------------------------------------------

    public function test_agent_can_change_password_via_force_change_password_page(): void
    {
        $agent = $this->createAgent(['must_change_password' => true]);

        Filament::setCurrentPanel(Filament::getPanel('agent'));

        Livewire::actingAs($agent, 'agent')
            ->test(ForceChangePassword::class)
            ->set('new_password', 'NewSecurePass1!')
            ->set('new_password_confirmation', 'NewSecurePass1!')
            ->call('save')
            ->assertRedirect('/agent');

        $this->assertFalse($agent->fresh()->must_change_password);
    }
}
