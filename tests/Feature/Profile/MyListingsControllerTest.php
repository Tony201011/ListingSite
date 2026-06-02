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

    public function test_owner_can_view_profile_details_from_my_listings(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;

        $profile->update([
            'name' => 'Detail Profile',
            'suburb' => 'Brisbane',
            'description' => 'Detailed profile description',
            'phone' => '0400000000',
        ]);

        $response = $this->actingAsProvider($user, $profile)->get(route('my-listings.profile.show', $profile));

        $response->assertOk();
        $response->assertSee('Detail Profile');
        $response->assertSee('Detailed profile description');
        $response->assertSee('Brisbane');
    }
}
