<?php

namespace Tests\Feature\Admin;

use App\Filament\Widgets\AccountStatsOverview;
use App\Filament\Widgets\ProviderStatsOverview;
use App\Models\ProviderProfile;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AccountStatsOverviewTest extends TestCase
{
    use RefreshDatabase;

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
            'account_status' => 'active',
            'email_verified_at' => now(),
            'is_blocked' => false,
        ], $overrides));

        ProviderProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
        ]);

        return $user;
    }

    public function test_account_management_page_shows_account_insights_widget(): void
    {
        $admin = $this->createAdmin();

        $response = $this->actingAs($admin, 'admin')->get('/admin/account-management/account');

        $response->assertOk();
        $response->assertSeeLivewire(AccountStatsOverview::class);
        $response->assertDontSeeLivewire(ProviderStatsOverview::class);
    }

    public function test_account_insights_counts_provider_accounts_not_provider_profiles(): void
    {
        $this->createAdmin();

        $active = $this->createProvider();
        $this->createProvider([
            'account_status' => 'inactive',
        ]);
        $this->createProvider([
            'account_status' => 'anonymized',
        ]);
        $this->createProvider([
            'is_blocked' => true,
        ]);
        $softDeletedAccount = $this->createProvider();
        $softDeletedAccount->delete();

        $extraProfile = ProviderProfile::create([
            'user_id' => $active->id,
            'name' => $active->name.' Extra',
            'slug' => 'provider-'.$active->id.'-extra',
        ]);
        $extraProfile->delete();

        User::factory()->create([
            'role' => User::ROLE_ADMIN,
        ]);

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $html = Livewire::test(AccountStatsOverview::class)->html();

        $this->assertStringContainsString('Account Insights', $html);
        $this->assertStringContainsString('Total Accounts', $html);
        $this->assertStringContainsString('Soft Deleted', $html);
        $this->assertStringContainsString('Anonymized', $html);
        $this->assertStringContainsString('Blocked', $html);
        $this->assertStringContainsString('5', $html);
        $this->assertStringContainsString('1', $html);
        $this->assertStringNotContainsString('Provider Insights', $html);
        $this->assertStringNotContainsString('Total Providers', $html);
    }
}
