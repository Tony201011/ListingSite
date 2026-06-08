<?php

namespace Tests\Feature;

use App\Models\Availability;
use App\Models\CreditLog;
use App\Models\ProfileImage;
use App\Models\ProviderListing;
use App\Models\ProviderProfile;
use App\Models\PurchaseTransaction;
use App\Models\Rate;
use App\Models\User;
use App\Models\Wallet;
use Database\Seeders\CategorySeeder;
use Database\Seeders\SiteSettingSeeder;
use Database\Seeders\TestAdvertiserAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestAdvertiserAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_test_advertiser_seeder_creates_a_verified_test_advertiser_account_with_demo_data(): void
    {
        $this->seed([
            CategorySeeder::class,
            SiteSettingSeeder::class,
            TestAdvertiserAccountSeeder::class,
        ]);

        $user = User::query()->where('email', TestAdvertiserAccountSeeder::EMAIL)->firstOrFail();

        // User account assertions
        $this->assertSame('Test Advertiser', $user->name);
        $this->assertSame(User::ROLE_TEST_ADVERTISER, $user->role);
        $this->assertFalse($user->is_blocked);
        $this->assertTrue($user->hasVerifiedEmail());

        // Main approved profile
        $profile = ProviderProfile::query()
            ->where('user_id', $user->id)
            ->where('name', 'Test Advertiser Profile')
            ->firstOrFail();

        $this->assertSame('approved', $profile->profile_status);
        $this->assertTrue($profile->is_blocked);   // Hidden from public search
        $this->assertNotNull($profile->free_listing_expires_at);
        $this->assertNotEmpty($profile->primary_identity);
        $this->assertNotEmpty($profile->attributes);
        $this->assertNotEmpty($profile->services_style);
        $this->assertNotEmpty($profile->services_provided);

        // Premium / featured tier fields are populated
        $this->assertTrue((bool) $profile->is_featured);
        $this->assertNotNull($profile->featured_expires_at);
        $this->assertNotNull($profile->home_featured_expires_at);
        $this->assertNotNull($profile->local_banner_expires_at);
        $this->assertNotNull($profile->home_banner_expires_at);

        // Pending profile for moderation-status demo
        $pendingProfile = ProviderProfile::query()
            ->where('user_id', $user->id)
            ->where('name', 'Test Advertiser (Pending Review)')
            ->firstOrFail();
        $this->assertSame('pending', $pendingProfile->profile_status);

        // Listing statuses (active, paused, inactive) linked to main profile
        $this->assertDatabaseHas('provider_listings', [
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'title' => 'Test Advertiser — Active Demo Listing',
            'is_live' => true,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('provider_listings', [
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'title' => 'Test Advertiser — Paused Demo Listing',
            'is_live' => false,
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('provider_listings', [
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'title' => 'Test Advertiser — Inactive Demo Listing',
            'is_live' => false,
            'is_active' => false,
        ]);

        // Primary profile image exists
        $this->assertDatabaseHas('profile_images', [
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'image_path' => 'https://picsum.photos/seed/demo-advertiser-1/400/400',
            'is_primary' => true,
        ]);

        // Rates seeded
        $this->assertGreaterThanOrEqual(
            4,
            Rate::where('provider_profile_id', $profile->id)->count(),
            'Expected at least 4 demo rates for the test advertiser profile'
        );

        // Availability schedule seeded (all 7 days)
        $this->assertSame(
            7,
            Availability::where('provider_profile_id', $profile->id)->count(),
            'Expected availability rows for all 7 days'
        );

        // Purchase transactions seeded (2 paid + 1 pending)
        $this->assertSame(
            2,
            PurchaseTransaction::where('user_id', $user->id)
                ->where('provider_profile_id', $profile->id)
                ->where('status', 'paid')
                ->count()
        );
        $this->assertSame(
            1,
            PurchaseTransaction::where('user_id', $user->id)
                ->where('provider_profile_id', $profile->id)
                ->where('status', 'pending')
                ->count()
        );

        // Credit logs seeded and linked to the profile
        $creditLogs = CreditLog::where('user_id', $user->id)
            ->where('reference_type', ProviderProfile::class)
            ->where('reference_id', $profile->id)
            ->get();

        $this->assertGreaterThan(0, $creditLogs->count(), 'Expected demo credit log entries');
        $this->assertTrue(
            $creditLogs->contains(fn ($log) => $log->amount > 0),
            'Expected at least one positive (credit) log entry'
        );
        $this->assertTrue(
            $creditLogs->contains(fn ($log) => $log->amount < 0),
            'Expected at least one negative (debit) log entry'
        );

        // Wallet exists for the profile
        $wallet = Wallet::where('provider_profile_id', $profile->id)->first();
        $this->assertNotNull($wallet, 'Expected a wallet record for the test advertiser profile');
        $this->assertGreaterThan(0, $wallet->current_balance, 'Expected a positive demo wallet balance');
    }

    public function test_test_advertiser_seeder_is_idempotent_and_restores_soft_deleted_demo_records(): void
    {
        $this->seed([
            CategorySeeder::class,
            SiteSettingSeeder::class,
            TestAdvertiserAccountSeeder::class,
        ]);

        $user = User::query()->where('email', TestAdvertiserAccountSeeder::EMAIL)->firstOrFail();

        // Pick the main approved profile for the soft-delete/restore test
        $profile = ProviderProfile::query()
            ->where('user_id', $user->id)
            ->where('name', 'Test Advertiser Profile')
            ->firstOrFail();
        $image = ProfileImage::query()->where('provider_profile_id', $profile->id)->firstOrFail();

        $image->delete();
        $profile->delete();
        $user->delete();

        $this->seed(TestAdvertiserAccountSeeder::class);

        $restoredUser = User::withTrashed()->where('email', TestAdvertiserAccountSeeder::EMAIL)->firstOrFail();
        $restoredMainProfile = ProviderProfile::withTrashed()
            ->where('user_id', $restoredUser->id)
            ->where('name', 'Test Advertiser Profile')
            ->firstOrFail();

        $this->assertNull($restoredUser->deleted_at);
        $this->assertNull($restoredMainProfile->deleted_at);
        $this->assertSame(1, User::withTrashed()->where('email', TestAdvertiserAccountSeeder::EMAIL)->count());

        // Both profiles (approved + pending) should exist after re-run
        $this->assertSame(
            2,
            ProviderProfile::withTrashed()->where('user_id', $restoredUser->id)->count()
        );

        // At least one image remains for the main profile
        $this->assertGreaterThanOrEqual(
            1,
            ProfileImage::withTrashed()->where('provider_profile_id', $restoredMainProfile->id)->count()
        );

        // All three listing variants are present after re-run
        $this->assertSame(
            3,
            ProviderListing::query()->where('provider_profile_id', $restoredMainProfile->id)->count()
        );
    }
}
