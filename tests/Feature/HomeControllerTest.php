<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\HideShowProfile;
use App\Models\OnlineUser;
use App\Models\Postcode;
use App\Models\ProfileView;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use App\Models\State;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class HomeControllerTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function createApprovedProvider(array $profileOverrides = []): User
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create(array_merge([
            'user_id' => $user->id,
            'name' => 'Test Escort',
            'slug' => 'test-escort-'.$user->id,
            'profile_status' => 'approved',
            'age' => 25,
        ], $profileOverrides));

        $this->createActiveOnlineUser($user, $profile->id);

        return $user;
    }

    private function createApprovedProviderWithSuburb(string $suburb, string $state, array $profileOverrides = []): User
    {
        // The signup/edit-profile autocomplete stores suburb in "Suburb, STATE postcode"
        // format (e.g. "Sydney, NSW 2000").  Use a recognisable dummy postcode so that
        // both the new-format LIKE match and the legacy postcode EXISTS check work.
        $storedSuburb = "{$suburb}, {$state} 0000";

        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
        ]);

        $profile = ProviderProfile::query()->create(array_merge([
            'user_id' => $user->id,
            'name' => 'Test Escort',
            'slug' => 'test-escort-'.$user->id,
            'profile_status' => 'approved',
            'age' => 25,
            'suburb' => $storedSuburb,
        ], $profileOverrides));

        $this->createActiveOnlineUser($user, $profile->id);

        Postcode::query()->create([
            'suburb' => $suburb,
            'state' => $state,
            'postcode' => '0000',
            'latitude' => 0,
            'longitude' => 0,
        ]);

        return $user;
    }

    private function createActiveOnlineUser(User $user, int $providerProfileId): void
    {
        OnlineUser::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $providerProfileId,
            'status' => 'online',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => now()->subMinutes(5),
            'online_expires_at' => now()->addMinutes(55),
        ]);
    }

    // ---------------------------------------------------------------
    // Basic rendering
    // ---------------------------------------------------------------

    public function test_home_page_returns_200(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_home_page_renders_correct_view(): void
    {
        $response = $this->get('/');

        $response->assertViewIs('frontend.home');
    }

    public function test_home_page_passes_required_view_data(): void
    {
        $response = $this->get('/');

        $response->assertViewHasAll([
            'profiles',
            'filterGroups',
            'allFilterCategories',
            'selectedCategoryIds',
            'selectedCategoryItems',
            'minAge',
            'maxAge',
            'minPrice',
            'maxPrice',
            'locationQuery',
            'girlsMode',
            'hasAgeFilter',
            'hasPriceFilter',
            'hasDistanceFilter',
            'maxSearchDistance',
            'userFavourites',
            'userBookmarks',
        ]);
    }

    public function test_favourites_page_resolves_legacy_numeric_favourite_ids_to_slugs(): void
    {
        $providerUser = $this->createApprovedProvider(['name' => 'Legacy Favourite', 'slug' => 'legacy-favourite']);
        $profile = ProviderProfile::query()->where('user_id', $providerUser->id)->firstOrFail();
        $viewer = User::factory()->create();

        Cache::put("favourites_user_{$viewer->id}", [(string) $profile->id], 60);

        $response = $this->actingAs($viewer)->get('/favourites');

        $response->assertStatus(200);
        $response->assertViewHas('userFavourites', ['legacy-favourite']);
        $profiles = $response->viewData('profiles');
        $this->assertCount(1, $profiles);
        $this->assertSame('legacy-favourite', $profiles[0]['slug']);
    }

    public function test_favourites_page_resolves_mixed_case_favourite_slugs_to_canonical_slug(): void
    {
        $this->createApprovedProvider(['name' => 'Case Favourite', 'slug' => 'case-favourite']);
        $viewer = User::factory()->create();

        Cache::put("favourites_user_{$viewer->id}", ['  CASE-FAVOURITE  '], 60);

        $response = $this->actingAs($viewer)->get('/favourites');

        $response->assertStatus(200);
        $response->assertViewHas('userFavourites', ['case-favourite']);
        $profiles = $response->viewData('profiles');
        $this->assertCount(1, $profiles);
        $this->assertSame('case-favourite', $profiles[0]['slug']);
    }

    public function test_home_page_shows_default_filter_values(): void
    {
        $response = $this->get('/');

        $response->assertViewHas('minAge', 18);
        $response->assertViewHas('maxAge', 40);
        $response->assertViewHas('minPrice', 150);
        $response->assertViewHas('maxPrice', 400);
        $response->assertViewHas('locationQuery', '');
        $response->assertViewHas('girlsMode', 'all');
        $response->assertViewHas('hasAgeFilter', false);
        $response->assertViewHas('hasPriceFilter', false);
    }

    // ---------------------------------------------------------------
    // Profile visibility
    // ---------------------------------------------------------------

    public function test_home_page_shows_approved_profiles(): void
    {
        $this->createApprovedProvider(['name' => 'Approved Escort', 'slug' => 'approved-escort']);

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $this->assertGreaterThanOrEqual(1, $profiles->total());
    }

    public function test_home_page_does_not_show_pending_profiles(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Pending Escort',
            'slug' => 'pending-escort',
            'profile_status' => 'pending',
        ]);

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $this->assertSame(0, $profiles->total());
    }

    public function test_home_page_does_not_show_blocked_profiles(): void
    {
        $this->createApprovedProvider([
            'name' => 'Blocked Escort',
            'slug' => 'blocked-escort',
            'is_blocked' => true,
        ]);

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $this->assertSame(0, $profiles->total());
    }

    public function test_home_page_does_not_show_soft_deleted_profiles(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Deleted Escort',
            'slug' => 'deleted-escort',
            'profile_status' => 'approved',
        ]);
        $profile->delete();

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $this->assertSame(0, $profiles->total());
    }

    public function test_home_page_does_not_show_hidden_profiles(): void
    {
        $user = $this->createApprovedProvider(['name' => 'Hidden Escort', 'slug' => 'hidden-escort']);
        $profile = ProviderProfile::query()->where('user_id', $user->id)->first();

        HideShowProfile::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'status' => 'hide',
        ]);

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $this->assertSame(0, $profiles->total());
    }

    public function test_home_page_shows_visible_profiles(): void
    {
        $user = $this->createApprovedProvider(['name' => 'Visible Escort', 'slug' => 'visible-escort']);
        $profile = ProviderProfile::query()->where('user_id', $user->id)->first();

        HideShowProfile::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'status' => 'show',
        ]);

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $this->assertSame(1, $profiles->total());
    }

    public function test_featured_profiles_appear_before_non_featured(): void
    {
        $this->createApprovedProvider(['name' => 'Regular', 'slug' => 'regular', 'is_featured' => false]);
        $this->createApprovedProvider(['name' => 'Featured', 'slug' => 'featured', 'is_featured' => true]);

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $items = collect($profiles->items());
        $this->assertSame('Featured', $items->first()['name']);
    }

    public function test_home_page_new_girls_mode_orders_by_latest_created_profile(): void
    {
        $olderUser = $this->createApprovedProvider(['name' => 'Older Escort', 'slug' => 'older-escort']);
        $newerUser = $this->createApprovedProvider(['name' => 'Newer Escort', 'slug' => 'newer-escort']);

        ProviderProfile::query()->where('user_id', $olderUser->id)->update([
            'created_at' => Carbon::now()->subDays(2),
        ]);
        ProviderProfile::query()->where('user_id', $newerUser->id)->update([
            'created_at' => Carbon::now()->subHour(),
        ]);

        $response = $this->get('/?girls=new');

        $response->assertViewHas('girlsMode', 'new');
        $profiles = $response->viewData('profiles');
        $items = collect($profiles->items());
        $this->assertSame('Newer Escort', $items->first()['name']);
    }

    public function test_home_page_popular_mode_orders_by_profile_view_count(): void
    {
        $popularUser = $this->createApprovedProvider(['name' => 'Popular Escort', 'slug' => 'popular-escort']);
        $lessPopularUser = $this->createApprovedProvider(['name' => 'Less Popular Escort', 'slug' => 'less-popular-escort']);

        $popularProfile = ProviderProfile::query()->where('user_id', $popularUser->id)->firstOrFail();
        $lessPopularProfile = ProviderProfile::query()->where('user_id', $lessPopularUser->id)->firstOrFail();

        ProfileView::query()->create(['user_id' => $popularUser->id, 'provider_profile_id' => $popularProfile->id, 'viewer_ip' => '1.1.1.1']);
        ProfileView::query()->create(['user_id' => $popularUser->id, 'provider_profile_id' => $popularProfile->id, 'viewer_ip' => '1.1.1.2']);
        ProfileView::query()->create(['user_id' => $lessPopularUser->id, 'provider_profile_id' => $lessPopularProfile->id, 'viewer_ip' => '1.1.1.3']);

        $response = $this->get('/?girls=popular');

        $response->assertViewHas('girlsMode', 'popular');
        $profiles = $response->viewData('profiles');
        $items = collect($profiles->items());
        $this->assertSame('Popular Escort', $items->first()['name']);
    }

    // ---------------------------------------------------------------
    // Filtering by escort name
    // ---------------------------------------------------------------

    public function test_home_page_filters_by_escort_name(): void
    {
        $this->createApprovedProvider(['name' => 'Unique Amber', 'slug' => 'unique-amber']);
        $this->createApprovedProvider(['name' => 'Different Belle', 'slug' => 'different-belle']);

        $response = $this->get('/?escort_name=Unique+Amber');

        $profiles = $response->viewData('profiles');
        $this->assertSame(1, $profiles->total());
        $this->assertSame('Unique Amber', collect($profiles->items())->first()['name']);
    }

    public function test_home_page_escort_name_filter_is_case_insensitive(): void
    {
        $this->createApprovedProvider(['name' => 'Jasmine Rose', 'slug' => 'jasmine-rose']);

        $response = $this->get('/?escort_name=jasmine');

        $profiles = $response->viewData('profiles');
        $this->assertGreaterThanOrEqual(1, $profiles->total());
    }

    public function test_home_page_escort_name_filter_with_no_match_returns_empty(): void
    {
        $this->createApprovedProvider(['name' => 'Amber', 'slug' => 'amber']);

        $response = $this->get('/?escort_name=NonExistentName12345');

        $profiles = $response->viewData('profiles');
        $this->assertSame(0, $profiles->total());
    }

    public function test_home_page_view_data_includes_escort_name_query(): void
    {
        $response = $this->get('/?escort_name=Ruby');

        $response->assertViewHas('escortNameQuery', 'Ruby');
    }

    public function test_home_page_hides_spotlight_sections_when_search_filters_are_active(): void
    {
        $this->createApprovedProvider([
            'name' => 'Spotlight Escort',
            'slug' => 'spotlight-escort',
            'home_banner_expires_at' => now()->addDay(),
            'local_banner_expires_at' => now()->addDay(),
        ]);

        $unfilteredResponse = $this->get('/');
        $unfilteredResponse->assertSeeText('Featured Spotlight');

        $filteredResponse = $this->get('/?escort_name=Spotlight+Escort');
        $filteredResponse->assertDontSeeText('Featured Spotlight');
        $filteredResponse->assertDontSeeText('Local Spotlight');
    }

    public function test_local_spotlight_shows_when_filtering_by_location_with_state(): void
    {
        $country = Country::query()->create(['name' => 'Australia', 'code' => 'AU']);
        $state = State::query()->create(['country_id' => $country->id, 'name' => 'Victoria']);

        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'VIC Local Escort',
            'slug' => 'vic-local-escort',
            'profile_status' => 'approved',
            'age' => 25,
            'state_id' => $state->id,
            'local_banner_expires_at' => now()->addDay(),
        ]);
        $this->createActiveOnlineUser($user, ProviderProfile::query()->where('user_id', $user->id)->value('id'));
        $this->createApprovedProviderWithSuburb('UNDERBOOL', 'VIC', [
            'name' => 'Regular Underbool Escort',
            'slug' => 'regular-underbool-escort',
        ]);

        // Local Spotlight should be visible when filtering by a location with a VIC state component.
        $response = $this->get('/?location=UNDERBOOL%2C+VIC');

        $response->assertSeeText('Local Spotlight');
        $profiles = $response->viewData('profiles');
        $profileNames = collect($profiles->items())->pluck('name');
        $this->assertTrue($profileNames->contains('Regular Underbool Escort'));
        $this->assertFalse($profileNames->contains('VIC Local Escort'));

        $localSpotlightNames = collect($response->viewData('localBannerProfiles'))->pluck('name');
        $this->assertTrue($localSpotlightNames->contains('VIC Local Escort'));
    }

    public function test_local_spotlight_filters_to_exact_suburb_and_state_when_state_relation_is_missing(): void
    {
        $macknadeUser = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $macknadeProfile = ProviderProfile::query()->create([
            'user_id' => $macknadeUser->id,
            'name' => 'Macknade Local Spotlight',
            'slug' => 'macknade-local-spotlight',
            'profile_status' => 'approved',
            'age' => 25,
            'suburb' => 'MACKNADE, QLD 4850',
            'local_banner_expires_at' => now()->addDay(),
        ]);
        $this->createActiveOnlineUser($macknadeUser, $macknadeProfile->id);

        $townsvilleUser = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $townsvilleProfile = ProviderProfile::query()->create([
            'user_id' => $townsvilleUser->id,
            'name' => 'Townsville Local Spotlight',
            'slug' => 'townsville-local-spotlight',
            'profile_status' => 'approved',
            'age' => 25,
            'suburb' => 'TOWNSVILLE, QLD 4810',
            'local_banner_expires_at' => now()->addDay(),
        ]);
        $this->createActiveOnlineUser($townsvilleUser, $townsvilleProfile->id);

        $this->createApprovedProviderWithSuburb('MACKNADE', 'QLD', [
            'name' => 'Regular Macknade Escort',
            'slug' => 'regular-macknade-escort',
        ]);

        $response = $this->get('/?location=MACKNADE%2C+QLD');

        $response->assertSeeText('Local Spotlight');
        $localSpotlightNames = collect($response->viewData('localBannerProfiles'))->pluck('name');
        $this->assertTrue($localSpotlightNames->contains('Macknade Local Spotlight'));
        $this->assertFalse($localSpotlightNames->contains('Townsville Local Spotlight'));
    }

    // ---------------------------------------------------------------
    // Filtering by location
    // ---------------------------------------------------------------

    public function test_home_page_view_data_includes_location_query(): void
    {
        $response = $this->get('/?location=Sydney');

        $response->assertViewHas('locationQuery', 'Sydney');
    }

    public function test_home_page_filters_by_exact_suburb_and_state_for_location_search(): void
    {
        $this->createApprovedProviderWithSuburb('Sydney', 'NSW', ['name' => 'Sydney Escort', 'slug' => 'sydney-escort']);
        $this->createApprovedProviderWithSuburb('Adelaide', 'SA', ['name' => 'Adelaide Escort', 'slug' => 'adelaide-escort']);

        $response = $this->get('/?location=Sydney%2C+NSW');

        $profiles = $response->viewData('profiles');
        $this->assertSame(1, $profiles->total());
        $this->assertSame('Sydney Escort', collect($profiles->items())->first()['name']);
    }

    public function test_home_page_location_filter_excludes_same_suburb_name_from_different_state(): void
    {
        // "Sydney" exists as a suburb name in both NSW and another state.
        // Searching for "Sydney, NSW" must NOT return the profile from the other state.
        $this->createApprovedProviderWithSuburb('Sydney', 'NSW', ['name' => 'Sydney NSW Escort', 'slug' => 'sydney-nsw-escort']);
        $this->createApprovedProviderWithSuburb('Sydney', 'VIC', ['name' => 'Sydney VIC Escort', 'slug' => 'sydney-vic-escort']);

        $response = $this->get('/?location=Sydney%2C+NSW');

        $profiles = $response->viewData('profiles');
        $this->assertSame(1, $profiles->total());
        $this->assertSame('Sydney NSW Escort', collect($profiles->items())->first()['name']);
    }

    public function test_home_page_location_filter_excludes_same_suburb_name_from_different_state_reversed(): void
    {
        // Mirror of the above: "Melbourne, VIC" must not return "Melbourne, NSW".
        $this->createApprovedProviderWithSuburb('Melbourne', 'VIC', ['name' => 'Melbourne VIC Escort', 'slug' => 'melbourne-vic-escort']);
        $this->createApprovedProviderWithSuburb('Melbourne', 'NSW', ['name' => 'Melbourne NSW Escort', 'slug' => 'melbourne-nsw-escort']);

        $response = $this->get('/?location=Melbourne%2C+VIC');

        $profiles = $response->viewData('profiles');
        $this->assertSame(1, $profiles->total());
        $this->assertSame('Melbourne VIC Escort', collect($profiles->items())->first()['name']);
    }

    // ---------------------------------------------------------------
    // Age filter
    // ---------------------------------------------------------------

    public function test_home_page_filters_by_age_range(): void
    {
        $this->createApprovedProvider(['name' => 'Young Escort', 'slug' => 'young-escort', 'age' => 19]);
        $this->createApprovedProvider(['name' => 'Older Escort', 'slug' => 'older-escort', 'age' => 35]);

        $response = $this->get('/?min_age=18&max_age=20');

        $profiles = $response->viewData('profiles');
        $names = collect($profiles->items())->pluck('name');
        $this->assertTrue($names->contains('Young Escort'));
        $this->assertFalse($names->contains('Older Escort'));
    }

    public function test_home_page_has_age_filter_flag_set_when_age_differs_from_defaults(): void
    {
        $response = $this->get('/?min_age=20&max_age=30');

        $response->assertViewHas('hasAgeFilter', true);
        $response->assertViewHas('minAge', 20);
        $response->assertViewHas('maxAge', 30);
    }

    public function test_home_page_age_filter_flag_false_when_defaults_used(): void
    {
        $response = $this->get('/?min_age=18&max_age=40');

        $response->assertViewHas('hasAgeFilter', false);
    }

    // ---------------------------------------------------------------
    // Validation
    // ---------------------------------------------------------------

    public function test_home_page_rejects_invalid_min_age(): void
    {
        $response = $this->get('/?min_age=10');

        $response->assertSessionHasErrors(['min_age']);
    }

    public function test_home_page_rejects_non_numeric_min_age(): void
    {
        $response = $this->get('/?min_age=abc');

        $response->assertSessionHasErrors(['min_age']);
    }

    public function test_home_page_rejects_invalid_location_too_long(): void
    {
        $response = $this->get('/?location='.str_repeat('a', 256));

        $response->assertSessionHasErrors(['location']);
    }

    public function test_home_page_rejects_invalid_user_lat_out_of_range(): void
    {
        $response = $this->get('/?user_lat=999&user_lng=150');

        $response->assertSessionHasErrors(['user_lat']);
    }

    public function test_home_page_rejects_invalid_user_lng_out_of_range(): void
    {
        $response = $this->get('/?user_lat=-33&user_lng=999');

        $response->assertSessionHasErrors(['user_lng']);
    }

    public function test_home_page_rejects_invalid_min_price(): void
    {
        $response = $this->get('/?min_price=not_a_number');

        $response->assertSessionHasErrors(['min_price']);
    }

    public function test_home_page_rejects_invalid_girls_mode(): void
    {
        $response = $this->get('/?girls=invalid-mode');

        $response->assertSessionHasErrors(['girls']);
    }

    // ---------------------------------------------------------------
    // Advanced search page
    // ---------------------------------------------------------------

    public function test_advanced_search_page_returns_200(): void
    {
        $response = $this->get(route('advanced-search'));

        $response->assertStatus(200);
    }

    public function test_advanced_search_page_renders_correct_view(): void
    {
        $response = $this->get(route('advanced-search'));

        $response->assertViewIs('frontend.advanced-search');
    }

    public function test_advanced_search_passes_required_view_data(): void
    {
        $response = $this->get(route('advanced-search'));

        $response->assertViewHasAll([
            'profiles',
            'filterGroups',
            'minAge',
            'maxAge',
            'minPrice',
            'maxPrice',
            'locationQuery',
        ]);
    }

    // ---------------------------------------------------------------
    // Site password middleware
    // ---------------------------------------------------------------

    public function test_home_page_redirects_to_site_password_when_protection_enabled(): void
    {
        SiteSetting::query()->create([
            'site_password' => 'secret123',
            'site_password_enabled' => true,
        ]);

        $response = $this->get('/');

        $response->assertRedirect('/site-password');
    }

    public function test_home_page_accessible_when_site_password_disabled(): void
    {
        SiteSetting::query()->create([
            'site_password' => 'secret123',
            'site_password_enabled' => false,
        ]);

        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_home_page_accessible_when_site_access_session_is_set(): void
    {
        SiteSetting::query()->create([
            'site_password' => 'secret123',
            'site_password_enabled' => true,
        ]);

        $response = $this->withSession(['site_access' => true])->get('/');

        $response->assertStatus(200);
    }

    public function test_admin_user_bypasses_site_password_protection(): void
    {
        SiteSetting::query()->create([
            'site_password' => 'secret123',
            'site_password_enabled' => true,
        ]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->get('/');

        $response->assertStatus(200);
    }
}
