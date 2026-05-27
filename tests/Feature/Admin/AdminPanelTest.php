<?php

namespace Tests\Feature\Admin;

use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use App\Models\User;
use App\Models\OnlineUser;
use Filament\Panel;
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
        $user = User::factory()->create(array_merge([
            'role' => User::ROLE_PROVIDER,
            'email_verified_at' => now(),
        ], $overrides));

        // Create a default profile for the provider
        ProviderProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
        ]);

        return $user;
    }

    // ---------------------------------------------------------------
    // Access control
    // ---------------------------------------------------------------

    public function test_guest_is_redirected_from_admin_panel_to_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }

    public function test_admin_login_route_bypasses_site_password_screen(): void
    {
        SiteSetting::create([
            'site_password' => 'secret123',
            'site_password_enabled' => true,
        ]);

        $response = $this->get('/admin/login');

        $response->assertOk();
        $response->assertSeeText('Email address');
        $response->assertDontSeeText('Enter Site Password');
        $response->assertDontSee('action="/site-password"', false);
    }

    public function test_admin_user_can_access_admin_panel(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->get('/admin');

        $response->assertOk();
    }

    public function test_provider_user_cannot_access_admin_panel(): void
    {
        $provider = $this->createProvider();
        $profile = $provider->providerProfiles()->first();

        $response = $this->actingAs($provider, 'admin')
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->get('/admin');

        $response->assertForbidden();
    }

    // ---------------------------------------------------------------
    // canAccessPanel logic
    // ---------------------------------------------------------------

    public function test_admin_can_access_admin_panel_via_can_access_panel(): void
    {
        $admin = $this->createAdmin();
        $panel = app(Panel::class)::make()->id('admin');

        $this->assertTrue($admin->canAccessPanel($panel));
    }

    public function test_provider_cannot_access_admin_panel_via_can_access_panel(): void
    {
        $provider = $this->createProvider();
        $panel = app(Panel::class)::make()->id('admin');

        $this->assertFalse($provider->canAccessPanel($panel));
    }

    // ---------------------------------------------------------------
    // Admin panel resource pages
    // ---------------------------------------------------------------

    public function test_admin_can_view_providers_listing_page(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->get('/admin/providers');

        $response->assertOk();
    }

    public function test_featured_filter_in_providers_listing_shows_only_featured_profiles(): void
    {
        $admin = $this->createAdmin();

        $featuredProvider = $this->createProvider(['name' => 'Featured Account']);
        $featuredProvider->providerProfiles()->first()->update([
            'name' => 'Featured Profile',
            'slug' => 'featured-profile',
            'is_featured' => true,
        ]);

        $regularProvider = $this->createProvider(['name' => 'Regular Account']);
        $regularProvider->providerProfiles()->first()->update([
            'name' => 'Regular Profile',
            'slug' => 'regular-profile',
            'is_featured' => false,
        ]);

        $response = $this->actingAs($admin, 'admin')->get('/admin/providers?filters[is_featured][value]=1');

        $response->assertOk();
        $response->assertSeeText('Featured Profile');
        $response->assertDontSeeText('Regular Profile');
    }

    public function test_provider_view_page_shows_online_status_for_each_profile_in_same_account(): void
    {
        $admin = $this->createAdmin();
        $provider = $this->createProvider(['name' => 'Multi Account']);

        $firstProfile = $provider->providerProfiles()->first();
        $firstProfile->update([
            'name' => 'Primary Profile',
            'slug' => 'primary-profile',
        ]);

        $secondProfile = ProviderProfile::query()->create([
            'user_id' => $provider->id,
            'name' => 'Second Profile',
            'slug' => 'second-profile',
            'profile_sequence' => 2,
        ]);

        OnlineUser::query()->create([
            'user_id' => $provider->id,
            'provider_profile_id' => $secondProfile->id,
            'status' => 'online',
        ]);

        $response = $this->actingAs($admin, 'admin')->get("/admin/providers/{$firstProfile->id}/view");

        $response->assertOk();
        $response->assertSeeText('Primary Profile');
        $response->assertSeeText('Second Profile');
        $response->assertSeeTextInOrder(['Primary Profile', 'Offline', 'Second Profile', 'Online']);
    }
}
