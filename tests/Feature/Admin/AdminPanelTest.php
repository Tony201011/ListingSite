<?php

namespace Tests\Feature\Admin;

use App\Filament\Admin\Pages\Dashboard;
use App\Filament\Widgets\AccountStatusChart;
use App\Filament\Widgets\AvailabilityChart;
use App\Filament\Widgets\FeaturedListingChart;
use App\Filament\Widgets\OnlineUsersChart;
use App\Filament\Widgets\PaymentPurchasesChart;
use App\Filament\Widgets\PaymentSalesChart;
use App\Filament\Widgets\PaymentStatsOverview;
use App\Filament\Widgets\ProfileStatusChart;
use App\Filament\Widgets\ProviderRegistrationsChart;
use App\Filament\Widgets\ProviderStatsOverview;
use App\Filament\Widgets\SiteVisitorsChart;
use App\Filament\Widgets\UniqueUsersChart;
use App\Filament\Widgets\VisitorStatsOverview;
use App\Models\AvailableNow;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
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

    public function test_admin_dashboard_shows_available_now_card(): void
    {
        $this->createAdmin();
        $provider = $this->createProvider();
        $profile = $provider->providerProfiles()->firstOrFail();

        AvailableNow::query()->create([
            'user_id' => $provider->id,
            'provider_profile_id' => $profile->id,
            'status' => 'online',
            'usage_date' => today(),
            'usage_count' => 1,
            'available_started_at' => now()->subMinutes(5),
            'available_expires_at' => now()->addMinutes(55),
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $this->assertContains(ProviderStatsOverview::class, app(Dashboard::class)->getWidgets());

        $html = Livewire::test(ProviderStatsOverview::class)->html();

        $this->assertStringContainsString('Available Now', $html);
        $this->assertStringContainsString('/admin/providers?tableFilters%5Bavailable_now_status%5D%5Bvalue%5D=online', $html);
    }

    public function test_admin_dashboard_shows_total_providers_card(): void
    {
        $this->createAdmin();
        $this->createProvider();
        $this->createProvider();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $html = Livewire::test(ProviderStatsOverview::class)->html();

        $this->assertStringContainsString('Total Providers', $html);
        $this->assertStringContainsString('2', $html);
    }

    public function test_admin_dashboard_registration_chart_shows_account_and_provider_series(): void
    {
        $this->createAdmin();
        $this->createProvider();
        User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $html = Livewire::test(ProviderRegistrationsChart::class)->html();

        $this->assertStringContainsString('Account Registrations', $html);
        $this->assertStringContainsString('Provider Registrations', $html);
    }

    public function test_admin_dashboard_registers_all_summary_and_chart_widgets(): void
    {
        $this->assertSame([
            VisitorStatsOverview::class,
            ProviderStatsOverview::class,
            ProviderRegistrationsChart::class,
            ProfileStatusChart::class,
            AccountStatusChart::class,
            SiteVisitorsChart::class,
            PaymentStatsOverview::class,
            UniqueUsersChart::class,
            PaymentSalesChart::class,
            PaymentPurchasesChart::class,
            OnlineUsersChart::class,
            AvailabilityChart::class,
            FeaturedListingChart::class,
        ], app(Dashboard::class)->getWidgets());
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
}
