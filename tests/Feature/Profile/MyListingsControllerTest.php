<?php

namespace Tests\Feature\Profile;

use App\Models\ProviderListing;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyListingsControllerTest extends TestCase
{
    use RefreshDatabase;

    private function createProvider(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
        ]);

        return $user;
    }

    private function actingAsProvider(User $user, ?ProviderProfile $profile = null): static
    {
        $profile ??= $user->providerProfiles()->orderBy('id')->first();

        return $this->actingAs($user)->withSession([
            'active_provider_profile_id' => $profile?->id,
        ]);
    }

    public function test_my_listings_search_matches_linked_profile_fields(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;

        $profile->update([
            'name' => 'Scarlett Search',
            'suburb' => 'Melbourne',
            'description' => 'CBD companion',
        ]);

        ProviderListing::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'title' => 'Evening Listing',
            'website_type' => 'adult',
        ]);

        $response = $this->actingAsProvider($user, $profile)->get(route('my-listings', [
            'q' => 'Scarlett',
        ]));

        $response->assertOk();
        $response->assertSee('Evening Listing');
    }

    public function test_my_listings_search_filters_profile_fallback_cards(): void
    {
        $user = $this->createProvider();
        $firstProfile = $user->providerProfile;
        $firstProfile->update([
            'name' => 'Gold Coast Profile',
            'suburb' => 'Gold Coast',
        ]);

        $secondProfile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Sydney Profile',
            'slug' => 'sydney-profile',
            'suburb' => 'Sydney',
        ]);

        $response = $this->actingAsProvider($user, $firstProfile)->get(route('my-listings', [
            'q' => 'Gold Coast',
        ]));

        $response->assertOk();
        $response->assertSee('Gold Coast Profile');
        $response->assertDontSee('Sydney Profile');
        $response->assertSee(route('my-listings.profile.show', $firstProfile), false);
        $response->assertDontSee(route('my-listings.profile.show', $secondProfile), false);
    }

    public function test_owner_is_redirected_to_profile_setting_from_my_listings_details(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;

        $response = $this->actingAsProvider($user, $profile)->get(route('my-listings.profile.show', $profile));

        $response->assertRedirect(route('profile-setting'));
        $response->assertSessionHas('active_provider_profile_id', $profile->id);
    }

    public function test_owner_is_redirected_to_photos_from_my_listings_gallery_route(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;

        $response = $this->actingAsProvider($user, $profile)->get(route('my-listings.profile.gallery', $profile));

        $response->assertRedirect(route('photos'));
        $response->assertSessionHas('active_provider_profile_id', $profile->id);
    }

    public function test_my_listings_expiring_filter_shows_only_profiles_expiring_within_seven_days(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;
        $profile->update(['free_listing_expires_at' => now()->addDays(3)]);

        $expiredProfile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Expired Profile',
            'slug' => 'expired-profile',
            'free_listing_expires_at' => now()->subDay(),
        ]);

        $futureProfile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Future Profile',
            'slug' => 'future-profile',
            'free_listing_expires_at' => now()->addDays(12),
        ]);

        ProviderListing::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'title' => 'Expiring Soon Listing',
            'website_type' => 'adult',
            'is_active' => true,
            'is_live' => true,
        ]);

        ProviderListing::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $expiredProfile->id,
            'title' => 'Already Expired Listing',
            'website_type' => 'adult',
            'is_active' => true,
            'is_live' => true,
        ]);

        ProviderListing::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $futureProfile->id,
            'title' => 'Far Future Listing',
            'website_type' => 'adult',
            'is_active' => true,
            'is_live' => true,
        ]);

        $response = $this->actingAsProvider($user, $profile)->get(route('my-listings', [
            'status' => 'expiring',
        ]));

        $response->assertOk();
        $response->assertSee('Expiring Soon Listing');
        $response->assertDontSee('Already Expired Listing');
        $response->assertDontSee('Far Future Listing');
    }

    public function test_my_listings_expired_filter_shows_only_expired_profiles(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;
        $profile->update(['free_listing_expires_at' => now()->subDay()]);

        $activeProfile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Active Profile',
            'slug' => 'active-profile',
            'free_listing_expires_at' => now()->addDays(5),
        ]);

        ProviderListing::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'title' => 'Expired Listing',
            'website_type' => 'adult',
            'is_active' => true,
            'is_live' => false,
        ]);

        ProviderListing::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $activeProfile->id,
            'title' => 'Active Listing',
            'website_type' => 'adult',
            'is_active' => true,
            'is_live' => true,
        ]);

        $response = $this->actingAsProvider($user, $profile)->get(route('my-listings', [
            'status' => 'expired',
        ]));

        $response->assertOk();
        $response->assertSee('Expired Listing');
        $response->assertDontSee('Active Listing');
    }
}
