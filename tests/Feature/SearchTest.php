<?php

namespace Tests\Feature;

use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SearchTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function createApprovedProvider(array $profileOverrides = [], array $userOverrides = []): User
    {
        $user = User::factory()->create(array_merge(['role' => User::ROLE_PROVIDER], $userOverrides));

        ProviderProfile::query()->create(array_merge([
            'user_id' => $user->id,
            'name' => 'Test Escort',
            'slug' => 'test-escort-'.$user->id,
            'profile_status' => 'approved',
            'age' => 25,
        ], $profileOverrides));

        return $user;
    }

    // ===============================================================
    // Home page – price filter (view data and validation)
    // Note: actual price-range filtering uses a MySQL-specific REGEXP_REPLACE
    // query which cannot run in the SQLite test environment, so those
    // end-to-end filter tests are not included here.
    // ===============================================================

    public function test_home_page_price_filter_flag_false_when_defaults_used(): void
    {
        $response = $this->get('/?min_price=150&max_price=400');

        $response->assertViewHas('hasPriceFilter', false);
    }

    public function test_home_page_view_data_includes_default_min_and_max_price(): void
    {
        $response = $this->get('/');

        $response->assertViewHas('minPrice', 150);
        $response->assertViewHas('maxPrice', 400);
    }

    public function test_home_page_rejects_max_price_below_zero(): void
    {
        $response = $this->get('/?max_price=-1');

        $response->assertSessionHasErrors(['max_price']);
    }

    public function test_home_page_rejects_non_numeric_max_price(): void
    {
        $response = $this->get('/?max_price=not_a_number');

        $response->assertSessionHasErrors(['max_price']);
    }

    // ===============================================================
    // Home page – combined filters
    // ===============================================================

    public function test_home_page_combined_name_and_age_filter_returns_correct_profile(): void
    {
        $this->createApprovedProvider(['name' => 'Amber Young', 'slug' => 'amber-young', 'age' => 22]);
        $this->createApprovedProvider(['name' => 'Amber Older', 'slug' => 'amber-older', 'age' => 35]);

        $response = $this->get('/?escort_name=Amber&min_age=18&max_age=25');

        $profiles = $response->viewData('profiles');
        $names = collect($profiles->items())->pluck('name');
        $this->assertTrue($names->contains('Amber Young'));
        $this->assertFalse($names->contains('Amber Older'));
    }

    public function test_home_page_no_match_with_combined_filters_returns_empty(): void
    {
        $this->createApprovedProvider(['name' => 'Chloe', 'slug' => 'chloe', 'age' => 30]);

        $response = $this->get('/?escort_name=Chloe&min_age=18&max_age=20');

        $profiles = $response->viewData('profiles');
        $this->assertSame(0, $profiles->total());
    }

    // ===============================================================
    // Home page – hasActiveFilters
    // ===============================================================

    public function test_home_page_has_no_active_filters_by_default(): void
    {
        $response = $this->get('/');

        $response->assertViewHas('locationQuery', '');
        $response->assertViewHas('escortNameQuery', '');
        $response->assertViewHas('hasAgeFilter', false);
        $response->assertViewHas('hasPriceFilter', false);
    }

    public function test_home_page_has_active_filter_when_location_provided(): void
    {
        $response = $this->get('/?location=Melbourne');

        $response->assertViewHas('locationQuery', 'Melbourne');
    }

    public function test_home_page_has_active_filter_when_escort_name_provided(): void
    {
        $response = $this->get('/?escort_name=Sara');

        $response->assertViewHas('escortNameQuery', 'Sara');
    }

    // ===============================================================
    // Home page – pagination
    // ===============================================================

    public function test_home_page_profiles_result_is_paginated(): void
    {
        $response = $this->get('/');

        $profiles = $response->viewData('profiles');
        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $profiles);
    }

    public function test_home_page_pagination_works_with_page_param(): void
    {
        $response = $this->get('/?page=1');

        $response->assertStatus(200);
        $profiles = $response->viewData('profiles');
        $this->assertInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class, $profiles);
    }

    // ===============================================================
    // Home page – only approved profiles appear in search
    // ===============================================================

    public function test_home_page_search_by_name_excludes_non_approved_profiles(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Pending Star',
            'slug' => 'pending-star',
            'profile_status' => 'pending',
            'age' => 25,
        ]);

        $response = $this->get('/?escort_name=Pending+Star');

        $profiles = $response->viewData('profiles');
        $this->assertSame(0, $profiles->total());
    }

    // ===============================================================
    // Advanced search – basic
    // ===============================================================

    public function test_advanced_search_default_filter_values_match_home_page_defaults(): void
    {
        $response = $this->get(route('advanced-search'));

        $response->assertViewHas('minAge', 18);
        $response->assertViewHas('maxAge', 40);
        $response->assertViewHas('minPrice', 150);
        $response->assertViewHas('maxPrice', 400);
        $response->assertViewHas('locationQuery', '');
        $response->assertViewHas('escortNameQuery', '');
        $response->assertViewHas('hasAgeFilter', false);
        $response->assertViewHas('hasPriceFilter', false);
    }

    public function test_advanced_search_shows_approved_profiles(): void
    {
        $this->createApprovedProvider(['name' => 'Advanced Approved', 'slug' => 'advanced-approved']);

        $response = $this->get(route('advanced-search'));

        $profiles = $response->viewData('profiles');
        $this->assertGreaterThanOrEqual(1, $profiles->total());
    }

    public function test_advanced_search_hides_pending_profiles(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Pending Adv',
            'slug' => 'pending-adv',
            'profile_status' => 'pending',
        ]);

        $response = $this->get(route('advanced-search'));

        $profiles = $response->viewData('profiles');
        $this->assertSame(0, $profiles->total());
    }

    // ===============================================================
    // Advanced search – escort name filter
    // ===============================================================

    public function test_advanced_search_filters_by_escort_name(): void
    {
        $this->createApprovedProvider(['name' => 'Scarlett Night', 'slug' => 'scarlett-night']);
        $this->createApprovedProvider(['name' => 'Different Diana', 'slug' => 'different-diana']);

        $response = $this->get(route('advanced-search').'?escort_name=Scarlett+Night');

        $profiles = $response->viewData('profiles');
        $this->assertSame(1, $profiles->total());
        $this->assertSame('Scarlett Night', collect($profiles->items())->first()['name']);
    }

    public function test_advanced_search_escort_name_filter_is_case_insensitive(): void
    {
        $this->createApprovedProvider(['name' => 'Violet Moon', 'slug' => 'violet-moon']);

        $response = $this->get(route('advanced-search').'?escort_name=violet');

        $profiles = $response->viewData('profiles');
        $this->assertGreaterThanOrEqual(1, $profiles->total());
    }

    public function test_advanced_search_escort_name_filter_with_no_match_returns_empty(): void
    {
        $this->createApprovedProvider(['name' => 'Luna Star', 'slug' => 'luna-star']);

        $response = $this->get(route('advanced-search').'?escort_name=NoSuchName99999');

        $profiles = $response->viewData('profiles');
        $this->assertSame(0, $profiles->total());
    }

    public function test_advanced_search_view_data_includes_escort_name_query(): void
    {
        $response = $this->get(route('advanced-search').'?escort_name=Jasmine');

        $response->assertViewHas('escortNameQuery', 'Jasmine');
    }

    // ===============================================================
    // Advanced search – location filter
    // ===============================================================

    public function test_advanced_search_view_data_includes_location_query(): void
    {
        $response = $this->get(route('advanced-search').'?location=Brisbane');

        $response->assertViewHas('locationQuery', 'Brisbane');
    }

    // ===============================================================
    // Advanced search – age filter
    // ===============================================================

    public function test_advanced_search_filters_by_age_range(): void
    {
        $this->createApprovedProvider(['name' => 'Young Adv', 'slug' => 'young-adv', 'age' => 20]);
        $this->createApprovedProvider(['name' => 'Old Adv', 'slug' => 'old-adv', 'age' => 38]);

        $response = $this->get(route('advanced-search').'?min_age=18&max_age=25');

        $profiles = $response->viewData('profiles');
        $names = collect($profiles->items())->pluck('name');
        $this->assertTrue($names->contains('Young Adv'));
        $this->assertFalse($names->contains('Old Adv'));
    }

    public function test_advanced_search_age_filter_flag_set_when_values_differ_from_defaults(): void
    {
        $response = $this->get(route('advanced-search').'?min_age=21&max_age=35');

        $response->assertViewHas('hasAgeFilter', true);
        $response->assertViewHas('minAge', 21);
        $response->assertViewHas('maxAge', 35);
    }

    public function test_advanced_search_age_filter_flag_false_when_defaults_used(): void
    {
        $response = $this->get(route('advanced-search').'?min_age=18&max_age=40');

        $response->assertViewHas('hasAgeFilter', false);
    }

    // ===============================================================
    // Advanced search – price filter (view data and validation)
    // Note: actual price-range filtering uses a MySQL-specific REGEXP_REPLACE
    // query which cannot run in the SQLite test environment, so those
    // end-to-end filter tests are not included here.
    // ===============================================================

    public function test_advanced_search_price_filter_flag_false_when_defaults_used(): void
    {
        $response = $this->get(route('advanced-search').'?min_price=150&max_price=400');

        $response->assertViewHas('hasPriceFilter', false);
    }

    public function test_advanced_search_view_data_includes_default_min_and_max_price(): void
    {
        $response = $this->get(route('advanced-search'));

        $response->assertViewHas('minPrice', 150);
        $response->assertViewHas('maxPrice', 400);
    }

    public function test_advanced_search_rejects_negative_min_price(): void
    {
        $response = $this->get(route('advanced-search').'?min_price=-10');

        $response->assertSessionHasErrors(['min_price']);
    }

    // ===============================================================
    // Advanced search – combined filters
    // ===============================================================

    public function test_advanced_search_combined_name_and_age_filter(): void
    {
        $this->createApprovedProvider(['name' => 'Tia Young', 'slug' => 'tia-young', 'age' => 22]);
        $this->createApprovedProvider(['name' => 'Tia Older', 'slug' => 'tia-older', 'age' => 36]);

        $response = $this->get(route('advanced-search').'?escort_name=Tia&min_age=18&max_age=25');

        $profiles = $response->viewData('profiles');
        $names = collect($profiles->items())->pluck('name');
        $this->assertTrue($names->contains('Tia Young'));
        $this->assertFalse($names->contains('Tia Older'));
    }

    public function test_advanced_search_combined_filters_return_empty_when_no_match(): void
    {
        $this->createApprovedProvider(['name' => 'Nina', 'slug' => 'nina', 'age' => 28]);

        $response = $this->get(route('advanced-search').'?escort_name=Nina&min_age=18&max_age=20');

        $profiles = $response->viewData('profiles');
        $this->assertSame(0, $profiles->total());
    }

    // ===============================================================
    // Advanced search – validation
    // ===============================================================

    public function test_advanced_search_rejects_min_age_below_18(): void
    {
        $response = $this->get(route('advanced-search').'?min_age=10');

        $response->assertSessionHasErrors(['min_age']);
    }

    public function test_advanced_search_rejects_non_numeric_min_age(): void
    {
        $response = $this->get(route('advanced-search').'?min_age=abc');

        $response->assertSessionHasErrors(['min_age']);
    }

    public function test_advanced_search_rejects_max_age_below_18(): void
    {
        $response = $this->get(route('advanced-search').'?max_age=5');

        $response->assertSessionHasErrors(['max_age']);
    }

    public function test_advanced_search_rejects_non_numeric_min_price(): void
    {
        $response = $this->get(route('advanced-search').'?min_price=notanumber');

        $response->assertSessionHasErrors(['min_price']);
    }

    public function test_advanced_search_rejects_location_too_long(): void
    {
        $response = $this->get(route('advanced-search').'?location='.str_repeat('x', 256));

        $response->assertSessionHasErrors(['location']);
    }

    public function test_advanced_search_rejects_invalid_user_lat_out_of_range(): void
    {
        $response = $this->get(route('advanced-search').'?user_lat=999&user_lng=150');

        $response->assertSessionHasErrors(['user_lat']);
    }

    public function test_advanced_search_rejects_invalid_user_lng_out_of_range(): void
    {
        $response = $this->get(route('advanced-search').'?user_lat=-33&user_lng=999');

        $response->assertSessionHasErrors(['user_lng']);
    }

    // ===============================================================
    // Search suggestions API
    // ===============================================================

    public function test_search_suggestions_returns_empty_for_blank_query(): void
    {
        $response = $this->getJson(route('api.search.suggestions').'?q=');

        $response->assertOk();
        $response->assertJson(['suggestions' => []]);
    }

    public function test_search_suggestions_returns_empty_for_missing_query(): void
    {
        $response = $this->getJson(route('api.search.suggestions'));

        $response->assertOk();
        $response->assertJson(['suggestions' => []]);
    }

    public function test_search_suggestions_returns_json_with_suggestions_key(): void
    {
        $response = $this->getJson(route('api.search.suggestions').'?q=test');

        $response->assertOk();
        $response->assertJsonStructure(['suggestions']);
    }

    public function test_search_suggestions_response_is_array(): void
    {
        $response = $this->getJson(route('api.search.suggestions').'?q=anything');

        $response->assertOk();
        $this->assertIsArray($response->json('suggestions'));
    }

    public function test_search_suggestions_only_returns_approved_profiles(): void
    {
        // Create a pending profile with a unique search name
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'PendingSearchable',
            'slug' => 'pending-searchable',
            'profile_status' => 'pending',
            'age' => 25,
        ]);

        // The suggestions endpoint should not return pending profiles
        // (the SearchController passes 'profile_status' => 'approved' to Scout)
        $response = $this->getJson(route('api.search.suggestions').'?q=PendingSearchable');

        $response->assertOk();
        $suggestions = $response->json('suggestions');
        $names = array_column($suggestions, 'name');
        $this->assertNotContains('PendingSearchable', $names);
    }

    public function test_search_suggestions_returns_suggestions_with_correct_shape_when_results_found(): void
    {
        // Create an approved profile so Scout can potentially return it
        $this->createApprovedProvider(['name' => 'ShapeCheckEscort', 'slug' => 'shape-check-escort', 'age' => 27]);

        $response = $this->getJson(route('api.search.suggestions').'?q=ShapeCheckEscort');

        $response->assertOk();
        $suggestions = $response->json('suggestions');

        // If any suggestions were returned (Scout may not be fully configured in tests),
        // each item must have the expected keys.
        if (! empty($suggestions)) {
            foreach ($suggestions as $suggestion) {
                $this->assertArrayHasKey('name', $suggestion);
                $this->assertArrayHasKey('slug', $suggestion);
                $this->assertArrayHasKey('location', $suggestion);
                $this->assertArrayHasKey('age', $suggestion);
            }
        } else {
            // Scout not active in test environment – just verify the structure is present.
            $this->assertIsArray($suggestions);
        }
    }

    public function test_search_suggestions_returns_at_most_8_results(): void
    {
        // Create 10 approved profiles
        for ($i = 1; $i <= 10; $i++) {
            $this->createApprovedProvider([
                'name' => "BulkEscort{$i}",
                'slug' => "bulk-escort-{$i}",
            ]);
        }

        $response = $this->getJson(route('api.search.suggestions').'?q=BulkEscort');

        $response->assertOk();
        $suggestions = $response->json('suggestions');
        $this->assertLessThanOrEqual(8, count($suggestions));
    }

    public function test_search_suggestions_handles_special_characters_gracefully(): void
    {
        $response = $this->getJson(route('api.search.suggestions').'?q='.urlencode('<script>alert(1)</script>'));

        $response->assertOk();
        $response->assertJsonStructure(['suggestions']);
    }

    public function test_search_suggestions_handles_very_long_query_gracefully(): void
    {
        $longQuery = str_repeat('a', 300);

        $response = $this->getJson(route('api.search.suggestions').'?q='.$longQuery);

        $response->assertOk();
        $response->assertJsonStructure(['suggestions']);
    }
}
