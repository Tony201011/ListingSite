<?php

namespace Tests\Feature;

use App\Models\AvailableNow;
use App\Models\Country;
use App\Models\HideShowProfile;
use App\Models\OnlineUser;
use App\Models\PhotoVerification;
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

        $profile = $this->createApprovedProfile($user, $profileOverrides);

        $this->createActiveOnlineUser($user, $profile->id);

        return $user;
    }

    private function createApprovedProfile(User $user, array $profileOverrides = []): ProviderProfile
    {
        return ProviderProfile::query()->create(array_merge([
            'user_id' => $user->id,
            'name' => 'Test Escort',
            'slug' => 'test-escort-'.$user->id,
            'profile_status' => 'approved',
            'age' => 25,
        ], $profileOverrides));
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
            'online_expires_at' => null,
        ]);
    }

    private function createActiveAvailableNow(User $user, int $providerProfileId): void
    {
        AvailableNow::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $providerProfileId,
            'status' => 'online',
            'usage_date' => today(),
            'usage_count' => 1,
            'available_started_at' => now()->subMinutes(5),
            'available_expires_at' => now()->addMinutes(55),
        ]);
    }

    private function createPhotoVerification(User $user, int $providerProfileId, string $status): void
    {
        PhotoVerification::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $providerProfileId,
            'photos' => [['path' => 'verification/test/photo.jpg']],
            'status' => $status,
            'submitted_at' => now(),
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
            'onlineCount',
        ]);
    }

    public function test_online_count_counts_unique_profiles_not_sessions(): void
    {
        // User A has 3 legacy online_users rows (provider_profile_id=NULL) — simulating
        // multiple sessions / tabs for the same user — plus one profile-linked row.
        $userA = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id' => $userA->id,
            'name' => 'Profile A',
            'slug' => 'profile-a',
            'profile_status' => 'approved',
            'age' => 25,
        ]);
        $profileA = ProviderProfile::query()->where('user_id', $userA->id)->first();
        // Profile-linked online_users row
        OnlineUser::query()->create([
            'user_id' => $userA->id,
            'provider_profile_id' => $profileA->id,
            'status' => 'online',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => now()->subMinutes(5),
            'online_expires_at' => null,
        ]);
        // 3 legacy (NULL provider_profile_id) rows for the same user — simulate multiple sessions
        foreach (range(1, 3) as $i) {
            OnlineUser::query()->create([
                'user_id' => $userA->id,
                'provider_profile_id' => null,
                'status' => 'online',
                'usage_date' => today(),
                'usage_count' => $i,
                'online_started_at' => now()->subMinutes(5),
                'online_expires_at' => null,
            ]);
        }

        // User B has a single profile-linked online_users row
        $userB = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id' => $userB->id,
            'name' => 'Profile B',
            'slug' => 'profile-b',
            'profile_status' => 'approved',
            'age' => 25,
        ]);
        $profileB = ProviderProfile::query()->where('user_id', $userB->id)->first();
        OnlineUser::query()->create([
            'user_id' => $userB->id,
            'provider_profile_id' => $profileB->id,
            'status' => 'online',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => now()->subMinutes(5),
            'online_expires_at' => null,
        ]);

        $response = $this->get('/');

        // onlineCount must be 2 (one per unique profile), not 4 (raw online_users rows) or 5
        $this->assertSame(2, $response->viewData('onlineCount'));
    }

    public function test_online_count_ignores_offline_sessions(): void
    {
        // One profile with an offline online_users row
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Offline Profile',
            'slug' => 'offline-profile-count',
            'profile_status' => 'approved',
            'age' => 25,
        ]);
        $profile = ProviderProfile::query()->where('user_id', $user->id)->first();
        OnlineUser::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'status' => 'offline',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => null,
            'online_expires_at' => null,
        ]);

        $response = $this->get('/');

        $this->assertSame(0, $response->viewData('onlineCount'));
    }

    public function test_online_count_matches_visible_profiles_on_current_page(): void
    {
        SiteSetting::query()->create([
            'home_page_records' => 2,
        ]);

        $this->createApprovedProvider(['name' => 'Visible One', 'slug' => 'visible-one']);
        $this->createApprovedProvider(['name' => 'Visible Two', 'slug' => 'visible-two']);
        $this->createApprovedProvider(['name' => 'Hidden By Pagination One', 'slug' => 'hidden-by-pagination-one']);
        $this->createApprovedProvider(['name' => 'Hidden By Pagination Two', 'slug' => 'hidden-by-pagination-two']);

        $response = $this->get('/');

        $this->assertSame(4, $response->viewData('onlineCount'));
        $this->assertCount(2, $response->viewData('profiles'));
    }

    public function test_home_page_lists_each_online_profile_for_same_provider_account(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $firstProfile = $this->createApprovedProfile($user, [
            'name' => 'Account Profile One',
            'slug' => 'account-profile-one',
        ]);
        $secondProfile = $this->createApprovedProfile($user, [
            'name' => 'Account Profile Two',
            'slug' => 'account-profile-two',
        ]);

        $this->createActiveOnlineUser($user, $firstProfile->id);
        $this->createActiveOnlineUser($user, $secondProfile->id);

        OnlineUser::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => null,
            'status' => 'online',
            'usage_date' => today(),
            'usage_count' => 3,
            'online_started_at' => now()->subMinutes(5),
            'online_expires_at' => null,
        ]);

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');

        $this->assertSame(2, $response->viewData('onlineCount'));
        $this->assertSame(2, $profiles->total());
        $this->assertSame(
            [$firstProfile->getEscortUrl(), $secondProfile->getEscortUrl()],
            collect($profiles->items())->pluck('profile_url')->sort()->values()->all()
        );
    }

    public function test_home_page_shows_verified_photo_badge_for_approved_photo_verification(): void
    {
        $user = $this->createApprovedProvider([
            'name' => 'Approved Photo Provider',
            'slug' => 'approved-photo-provider',
            'is_verified' => false,
        ]);

        $this->createPhotoVerification($user, $user->providerProfile->id, 'approved');

        $response = $this->get('/');

        $profiles = collect($response->viewData('profiles'))->keyBy('name');

        $this->assertTrue($profiles['Approved Photo Provider']['verified']);
        $response->assertSee('Verified Photo');
    }

    public function test_home_page_does_not_show_verified_photo_badge_without_approved_photo_verification(): void
    {
        $user = $this->createApprovedProvider([
            'name' => 'Pending Photo Provider',
            'slug' => 'pending-photo-provider',
            'is_verified' => true,
        ]);

        $this->createPhotoVerification($user, $user->providerProfile->id, 'pending');

        $response = $this->get('/');

        $profiles = collect($response->viewData('profiles'))->keyBy('name');

        $this->assertFalse($profiles['Pending Photo Provider']['verified']);
        $response->assertDontSee('Verified Photo');
    }

    public function test_home_page_displays_online_user_counter_when_profiles_are_online(): void
    {
        $this->createApprovedProvider(['name' => 'Online Escort', 'slug' => 'online-escort-counter']);

        $response = $this->get('/');

        $response->assertSee('online');
    }

    public function test_home_page_renders_search_inputs_in_escorts_navigation_menus(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $this->assertSame(2, substr_count($response->getContent(), 'placeholder="Search escorts menu"'));
        $this->assertSame(2, substr_count($response->getContent(), 'No matching escorts found.'));
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

    public function test_favourites_listing_shows_both_available_and_online_badges_when_both_statuses_are_active(): void
    {
        $provider = $this->createApprovedProvider([
            'name' => 'Favourite Badge Escort',
            'slug' => 'favourite-badge-escort',
        ]);
        $profileId = ProviderProfile::query()->where('user_id', $provider->id)->value('id');
        $this->createActiveAvailableNow($provider, $profileId);

        $viewer = User::factory()->create();
        Cache::put("favourites_user_{$viewer->id}", ['favourite-badge-escort'], 60);

        $response = $this->actingAs($viewer)->get('/favourites');

        $response->assertOk();
        $response->assertSeeText('Available Now');
        $response->assertSeeText('Online Now');
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

    public function test_home_listing_shows_both_available_and_online_badges_when_both_statuses_are_active(): void
    {
        $user = $this->createApprovedProvider([
            'name' => 'Badge Test Escort',
            'slug' => 'badge-test-escort',
        ]);
        $profileId = ProviderProfile::query()->where('user_id', $user->id)->value('id');
        $this->createActiveAvailableNow($user, $profileId);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSeeText('Available Now');
        $response->assertSeeText('Online Now');
    }

    public function test_advanced_search_listing_shows_both_available_and_online_badges_when_both_statuses_are_active(): void
    {
        $user = $this->createApprovedProvider([
            'name' => 'Advanced Badge Test Escort',
            'slug' => 'advanced-badge-test-escort',
        ]);
        $profileId = ProviderProfile::query()->where('user_id', $user->id)->value('id');
        $this->createActiveAvailableNow($user, $profileId);

        $response = $this->get(route('advanced-search'));

        $response->assertOk();
        $response->assertSeeText('Available Now');
        $response->assertSeeText('Online Now');
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

    public function test_home_page_hides_featured_sections_when_search_filters_are_active(): void
    {
        $this->createApprovedProvider([
            'name' => 'Featured Escort',
            'slug' => 'featured-escort',
            'home_banner_expires_at' => now()->addDay(),
            'local_banner_expires_at' => now()->addDay(),
        ]);

        $unfilteredResponse = $this->get('/');
        $this->assertNotEmpty($unfilteredResponse->viewData('homeBannerProfiles'));

        $filteredResponse = $this->get('/?escort_name=Featured+Escort');
        $this->assertEmpty($filteredResponse->viewData('homeBannerProfiles'));
        $this->assertEmpty($filteredResponse->viewData('localBannerProfiles'));
    }

    public function test_local_featured_profile_appears_in_main_results_when_escort_name_filter_is_active(): void
    {
        $country = Country::query()->create(['name' => 'Australia', 'code' => 'AU']);
        $state = State::query()->create(['country_id' => $country->id, 'name' => 'New South Wales']);

        $this->createApprovedProviderWithSuburb('Sydney', 'NSW', [
            'name' => 'Sydney Local Escort',
            'slug' => 'sydney-local-escort',
            'state_id' => $state->id,
            'local_banner_expires_at' => now()->addDay(),
        ]);

        // Searching by escort_name + location with state: the profile has an active local_banner_expires_at
        // but featured is hidden when escort_name filter is active.  The profile must still appear in the
        // main results (the featured exclusion filter must NOT be applied).
        $response = $this->get('/?escort_name=Sydney+Local+Escort&location=Sydney%2C+NSW');

        $response->assertDontSeeText('Local Featured');
        $profiles = $response->viewData('profiles');
        $profileNames = collect($profiles->items())->pluck('name');
        $this->assertTrue($profileNames->contains('Sydney Local Escort'));
    }

    public function test_local_featured_shows_when_filtering_by_location_with_state(): void
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
            'suburb' => 'UNDERBOOL, VIC 0000',
            'local_banner_expires_at' => now()->addDay(),
        ]);
        $this->createActiveOnlineUser($user, ProviderProfile::query()->where('user_id', $user->id)->value('id'));
        $this->createApprovedProviderWithSuburb('UNDERBOOL', 'VIC', [
            'name' => 'Regular Underbool Escort',
            'slug' => 'regular-underbool-escort',
        ]);

        // Local Featured should be visible when filtering by a location with a VIC state component.
        $response = $this->get('/?location=UNDERBOOL%2C+VIC');

        $response->assertSeeText('Local Featured');
        $profiles = $response->viewData('profiles');
        $profileNames = collect($profiles->items())->pluck('name');
        $this->assertTrue($profileNames->contains('Regular Underbool Escort'));
        $this->assertFalse($profileNames->contains('VIC Local Escort'));

        $localFeaturedNames = collect($response->viewData('localBannerProfiles'))->pluck('name');
        $this->assertTrue($localFeaturedNames->contains('VIC Local Escort'));
    }

    public function test_local_featured_filters_by_exact_location_when_location_contains_suburb_and_state(): void
    {
        $macknadeUser = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $macknadeProfile = ProviderProfile::query()->create([
            'user_id' => $macknadeUser->id,
            'name' => 'Macknade Local Featured',
            'slug' => 'macknade-local-featured',
            'profile_status' => 'approved',
            'age' => 25,
            'suburb' => 'MACKNADE, QLD 4850',
            'local_banner_expires_at' => now()->addDay(),
        ]);
        $this->createActiveOnlineUser($macknadeUser, $macknadeProfile->id);

        $townsvilleUser = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $townsvilleProfile = ProviderProfile::query()->create([
            'user_id' => $townsvilleUser->id,
            'name' => 'Townsville Local Featured',
            'slug' => 'townsville-local-featured',
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

        $response->assertSeeText('Local Featured');
        $localFeaturedNames = collect($response->viewData('localBannerProfiles'))->pluck('name');
        $this->assertTrue($localFeaturedNames->contains('Macknade Local Featured'));
        $this->assertFalse($localFeaturedNames->contains('Townsville Local Featured'));
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
    // Online status visibility filter
    // ---------------------------------------------------------------

    public function test_home_page_hides_profile_with_offline_online_user_record(): void
    {
        SiteSetting::query()->create(['online_filter_enabled' => true]);

        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Offline Escort',
            'slug' => 'offline-escort',
            'profile_status' => 'approved',
            'age' => 25,
        ]);

        // Profile has used the online feature before, but is currently offline
        OnlineUser::query()->create([
            'user_id' => null,
            'provider_profile_id' => $profile->id,
            'status' => 'offline',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => null,
            'online_expires_at' => null,
        ]);

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $names = collect($profiles->items())->pluck('name');
        $this->assertFalse($names->contains('Offline Escort'));
    }

    public function test_home_page_shows_profile_with_online_status_regardless_of_expires_at(): void
    {
        SiteSetting::query()->create(['online_filter_enabled' => true]);

        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Online Escort With Past Expires',
            'slug' => 'online-escort-with-past-expires',
            'profile_status' => 'approved',
            'age' => 25,
        ]);

        // Profile has status = 'online'; online_expires_at is no longer enforced
        OnlineUser::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'status' => 'online',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => now()->subHours(2),
            'online_expires_at' => now()->subHour(),
        ]);

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $names = collect($profiles->items())->pluck('name');
        $this->assertTrue($names->contains('Online Escort With Past Expires'));
    }

    public function test_home_page_hides_profile_with_no_online_user_record(): void
    {
        SiteSetting::query()->create(['online_filter_enabled' => true]);

        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'New Escort',
            'slug' => 'new-escort',
            'profile_status' => 'approved',
            'age' => 25,
        ]);
        // No OnlineUser record created — profile has never used the online feature

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $names = collect($profiles->items())->pluck('name');
        $this->assertFalse($names->contains('New Escort'));
    }

    public function test_home_page_includes_profile_when_legacy_online_user_row_is_linked_by_user_only(): void
    {
        SiteSetting::query()->create(['online_filter_enabled' => true]);

        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Legacy Linked Escort',
            'slug' => 'legacy-linked-escort',
            'profile_status' => 'approved',
            'age' => 25,
        ]);

        OnlineUser::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => null,
            'status' => 'online',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => now()->subMinutes(5),
            'online_expires_at' => null,
        ]);

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $names = collect($profiles->items())->pluck('name');
        $this->assertTrue($names->contains('Legacy Linked Escort'));
    }

    public function test_home_page_hides_profile_when_profile_linked_row_is_offline_even_if_legacy_online_row_exists(): void
    {
        SiteSetting::query()->create(['online_filter_enabled' => true]);

        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Legacy Online With Offline Profile Row Escort',
            'slug' => 'legacy-online-offline-profile-row-escort',
            'profile_status' => 'approved',
            'age' => 25,
        ]);

        OnlineUser::query()->create([
            'user_id' => null,
            'provider_profile_id' => $profile->id,
            'status' => 'offline',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => null,
            'online_expires_at' => null,
        ]);

        OnlineUser::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => null,
            'status' => 'online',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => now()->subMinutes(5),
            'online_expires_at' => null,
        ]);

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $names = collect($profiles->items())->pluck('name');
        $this->assertFalse($names->contains('Legacy Online With Offline Profile Row Escort'));
    }

    public function test_home_page_marks_legacy_online_profile_active_even_when_user_has_other_profile_linked_rows(): void
    {
        SiteSetting::query()->create(['online_filter_enabled' => true]);

        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $legacyProfile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Legacy Active Escort',
            'slug' => 'legacy-active-escort',
            'profile_status' => 'approved',
            'age' => 25,
        ]);
        $otherProfile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Other Profile Escort',
            'slug' => 'other-profile-escort',
            'profile_status' => 'approved',
            'age' => 25,
        ]);

        OnlineUser::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $otherProfile->id,
            'status' => 'offline',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => null,
            'online_expires_at' => null,
        ]);

        OnlineUser::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => null,
            'status' => 'online',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => now()->subMinutes(5),
            'online_expires_at' => null,
        ]);

        $response = $this->get('/');
        $profiles = collect($response->viewData('profiles')->items());
        $legacyProfileData = $profiles->firstWhere('name', $legacyProfile->name);

        $this->assertNotNull($legacyProfileData);
        $this->assertTrue($legacyProfileData['active']);
    }

    public function test_home_page_hides_featured_profile_when_offline(): void
    {
        SiteSetting::query()->create(['online_filter_enabled' => true]);

        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Featured Offline Escort',
            'slug' => 'featured-offline-escort',
            'profile_status' => 'approved',
            'age' => 25,
            'is_featured' => true,
        ]);

        // Profile is featured but currently offline
        OnlineUser::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'status' => 'offline',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => null,
            'online_expires_at' => null,
        ]);

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $names = collect($profiles->items())->pluck('name');
        $this->assertFalse($names->contains('Featured Offline Escort'));
    }

    public function test_home_featured_slider_hides_offline_profiles_even_when_home_banner_is_active(): void
    {
        SiteSetting::query()->create(['online_filter_enabled' => true]);

        $this->createApprovedProvider([
            'name' => 'Online Featured Escort',
            'slug' => 'online-featured-escort',
            'home_banner_expires_at' => now()->addDay(),
        ]);

        $offlineUser = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $offlineProfile = ProviderProfile::query()->create([
            'user_id' => $offlineUser->id,
            'name' => 'Offline Featured Escort',
            'slug' => 'offline-featured-escort',
            'profile_status' => 'approved',
            'age' => 25,
            'home_banner_expires_at' => now()->addDay(),
        ]);
        OnlineUser::query()->create([
            'user_id' => $offlineUser->id,
            'provider_profile_id' => $offlineProfile->id,
            'status' => 'offline',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => null,
            'online_expires_at' => null,
        ]);

        $response = $this->get('/');

        $homeFeaturedNames = collect($response->viewData('homeBannerProfiles'))->pluck('name');
        $this->assertTrue($homeFeaturedNames->contains('Online Featured Escort'));
        $this->assertFalse($homeFeaturedNames->contains('Offline Featured Escort'));
    }

    public function test_featured_page_only_shows_online_profiles_for_all_featured_tiers(): void
    {
        SiteSetting::query()->create(['online_filter_enabled' => true]);

        $this->createApprovedProvider([
            'name' => 'Online Home Banner',
            'slug' => 'online-home-banner',
            'home_banner_expires_at' => now()->addDay(),
        ]);
        $this->createApprovedProvider([
            'name' => 'Online Home Featured',
            'slug' => 'online-home-featured',
            'home_featured_expires_at' => now()->addDay(),
        ]);
        $this->createApprovedProvider([
            'name' => 'Online Local Banner',
            'slug' => 'online-local-banner',
            'local_banner_expires_at' => now()->addDay(),
        ]);
        $this->createApprovedProvider([
            'name' => 'Online Featured',
            'slug' => 'online-featured',
            'featured_expires_at' => now()->addDay(),
        ]);

        $offlineUser = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $offlineProfile = ProviderProfile::query()->create([
            'user_id' => $offlineUser->id,
            'name' => 'Offline Tier Escort',
            'slug' => 'offline-tier-escort',
            'profile_status' => 'approved',
            'age' => 25,
            'home_banner_expires_at' => now()->addDay(),
            'home_featured_expires_at' => now()->addDay(),
            'local_banner_expires_at' => now()->addDay(),
            'featured_expires_at' => now()->addDay(),
        ]);
        OnlineUser::query()->create([
            'user_id' => $offlineUser->id,
            'provider_profile_id' => $offlineProfile->id,
            'status' => 'offline',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => null,
            'online_expires_at' => null,
        ]);

        $response = $this->get('/featured');

        $response->assertOk();
        $this->assertTrue(collect($response->viewData('homeBannerProfiles'))->pluck('name')->contains('Online Home Banner'));
        $this->assertTrue(collect($response->viewData('homeFeaturedProfiles'))->pluck('name')->contains('Online Home Featured'));
        $this->assertTrue(collect($response->viewData('localBannerProfiles'))->pluck('name')->contains('Online Local Banner'));
        $this->assertTrue(collect($response->viewData('featuredProfiles'))->pluck('name')->contains('Online Featured'));

        $this->assertFalse(collect($response->viewData('homeBannerProfiles'))->pluck('name')->contains('Offline Tier Escort'));
        $this->assertFalse(collect($response->viewData('homeFeaturedProfiles'))->pluck('name')->contains('Offline Tier Escort'));
        $this->assertFalse(collect($response->viewData('localBannerProfiles'))->pluck('name')->contains('Offline Tier Escort'));
        $this->assertFalse(collect($response->viewData('featuredProfiles'))->pluck('name')->contains('Offline Tier Escort'));
    }

    public function test_home_page_hides_offline_profile_even_when_online_filter_disabled(): void
    {
        SiteSetting::query()->create(['online_filter_enabled' => false]);

        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Offline No Filter Escort',
            'slug' => 'offline-no-filter-escort',
            'profile_status' => 'approved',
            'age' => 25,
        ]);
        // No OnlineUser record — profile has never been set online

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $names = collect($profiles->items())->pluck('name');
        $this->assertFalse($names->contains('Offline No Filter Escort'));
    }

    public function test_home_page_hides_offline_profile_when_online_filter_setting_missing(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Offline Default Filter Escort',
            'slug' => 'offline-default-filter-escort',
            'profile_status' => 'approved',
            'age' => 25,
        ]);

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $names = collect($profiles->items())->pluck('name');
        $this->assertFalse($names->contains('Offline Default Filter Escort'));
    }

    public function test_home_page_hides_offline_profile_when_online_filter_enabled(): void
    {
        SiteSetting::query()->create(['online_filter_enabled' => true]);

        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Offline Filter Escort',
            'slug' => 'offline-filter-escort',
            'profile_status' => 'approved',
            'age' => 25,
        ]);
        // No OnlineUser record

        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $names = collect($profiles->items())->pluck('name');
        $this->assertFalse($names->contains('Offline Filter Escort'));
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

    public function test_site_password_submit_sets_site_access_session(): void
    {
        SiteSetting::query()->create([
            'site_password' => 'secret123',
            'site_password_enabled' => true,
        ]);

        $response = $this->post('/site-password', [
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('site_access', true);
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

    public function test_home_banner_hidden_when_location_filter_active_and_no_local_banners(): void
    {
        // A profile with an active home banner (national).
        $this->createApprovedProvider([
            'name' => 'National Banner Escort',
            'slug' => 'national-banner-escort',
            'home_banner_expires_at' => now()->addDay(),
        ]);

        // Unfiltered home page: home banner should be visible.
        $unfilteredResponse = $this->get('/');
        $this->assertNotEmpty($unfilteredResponse->viewData('homeBannerProfiles'));
        $unfilteredResponse->assertSeeText('Featured');

        // Location-filtered page with no matching profiles and no local banners:
        // home banner must NOT be shown even though homeBannerProfiles is non-empty.
        $filteredResponse = $this->get('/?location=AMBROSE%2C+QLD');
        $this->assertNotEmpty($filteredResponse->viewData('homeBannerProfiles'));
        $this->assertEmpty($filteredResponse->viewData('localBannerProfiles'));
        $filteredResponse->assertDontSeeText('Featured');
    }
}
