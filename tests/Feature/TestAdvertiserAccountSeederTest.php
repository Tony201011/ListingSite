<?php

namespace Tests\Feature;

use App\Models\ProfileImage;
use App\Models\ProviderListing;
use App\Models\ProviderProfile;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\SiteSettingSeeder;
use Database\Seeders\TestAdvertiserAccountSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TestAdvertiserAccountSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_test_advertiser_seeder_creates_a_verified_provider_account_with_demo_profile_data(): void
    {
        $this->seed([
            CategorySeeder::class,
            SiteSettingSeeder::class,
            TestAdvertiserAccountSeeder::class,
        ]);

        $user = User::query()->where('email', TestAdvertiserAccountSeeder::EMAIL)->firstOrFail();
        $profile = ProviderProfile::query()
            ->where('user_id', $user->id)
            ->where('name', 'Test Advertiser Profile')
            ->firstOrFail();

        $this->assertSame('Test Advertiser', $user->name);
        $this->assertSame(User::ROLE_PROVIDER, $user->role);
        $this->assertFalse($user->is_blocked);
        $this->assertTrue($user->hasVerifiedEmail());
        $this->assertSame('approved', $profile->profile_status);
        $this->assertTrue($profile->is_blocked);
        $this->assertNotNull($profile->free_listing_expires_at);
        $this->assertNotEmpty($profile->primary_identity);
        $this->assertNotEmpty($profile->attributes);
        $this->assertNotEmpty($profile->services_style);
        $this->assertNotEmpty($profile->services_provided);

        $this->assertDatabaseHas('provider_listings', [
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'title' => 'Test Advertiser Demo Listing',
            'is_live' => false,
            'is_active' => false,
        ]);

        $this->assertDatabaseHas('profile_images', [
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'image_path' => 'https://images.example.com/demo/test-advertiser.jpg',
            'is_primary' => true,
        ]);
    }

    public function test_test_advertiser_seeder_is_idempotent_and_restores_soft_deleted_demo_records(): void
    {
        $this->seed([
            CategorySeeder::class,
            SiteSettingSeeder::class,
            TestAdvertiserAccountSeeder::class,
        ]);

        $user = User::query()->where('email', TestAdvertiserAccountSeeder::EMAIL)->firstOrFail();
        $profile = ProviderProfile::query()->where('user_id', $user->id)->firstOrFail();
        $image = ProfileImage::query()->where('provider_profile_id', $profile->id)->firstOrFail();

        $image->delete();
        $profile->delete();
        $user->delete();

        $this->seed(TestAdvertiserAccountSeeder::class);

        $restoredUser = User::withTrashed()->where('email', TestAdvertiserAccountSeeder::EMAIL)->firstOrFail();
        $restoredProfile = ProviderProfile::withTrashed()->where('user_id', $restoredUser->id)->firstOrFail();

        $this->assertNull($restoredUser->deleted_at);
        $this->assertNull($restoredProfile->deleted_at);
        $this->assertSame(1, User::withTrashed()->where('email', TestAdvertiserAccountSeeder::EMAIL)->count());
        $this->assertSame(1, ProviderProfile::withTrashed()->where('user_id', $restoredUser->id)->count());
        $this->assertSame(1, ProfileImage::withTrashed()->where('provider_profile_id', $restoredProfile->id)->count());
        $this->assertSame(1, ProviderListing::query()->where('provider_profile_id', $restoredProfile->id)->count());
    }
}
