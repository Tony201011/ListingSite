<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\Rate;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileShowControllerTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function createApprovedProvider(array $profileOverrides = []): User
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        ProviderProfile::query()->create(array_merge([
            'user_id'        => $user->id,
            'name'           => 'Jade',
            'slug'           => 'jade010-10',
            'profile_status' => 'approved',
            'age'            => 24,
        ], $profileOverrides));

        return $user;
    }

    // ---------------------------------------------------------------
    // Basic rendering
    // ---------------------------------------------------------------

    public function test_profile_show_returns_200_for_existing_approved_profile(): void
    {
        $this->createApprovedProvider();

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $response->assertStatus(200);
    }

    public function test_profile_show_renders_correct_view(): void
    {
        $this->createApprovedProvider();

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $response->assertViewIs('frontend.profile-show');
    }

    public function test_profile_show_passes_required_view_data_keys(): void
    {
        $this->createApprovedProvider();

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $response->assertViewHasAll([
            'profile',
            'nearbyProfiles',
            'selectedCategoryNames',
            'selectedCategoriesByGroup',
            'profileStats',
            'prevProfile',
            'nextProfile',
        ]);
    }

    // ---------------------------------------------------------------
    // 404 cases
    // ---------------------------------------------------------------

    public function test_profile_show_returns_404_for_unknown_slug(): void
    {
        $response = $this->get(route('profile.show', ['slug' => 'does-not-exist']));

        $response->assertStatus(404);
    }

    public function test_profile_show_returns_200_for_pending_profile(): void
    {
        // GetProfileShowData does not filter by profile_status on the show page,
        // so pending profiles are publicly accessible just like approved ones.
        // We also need at least one approved profile so the adjacent-navigation
        // queries (which DO filter by 'approved') return a non-empty slug and the
        // view can generate its prev/next route URLs without throwing.
        $this->createApprovedProvider(['name' => 'Approved', 'slug' => 'approved-001']);

        $pendingUser = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id'        => $pendingUser->id,
            'name'           => 'Pending Escort',
            'slug'           => 'pending-profile',
            'profile_status' => 'pending',
        ]);

        $response = $this->get(route('profile.show', ['slug' => 'pending-profile']));

        $response->assertStatus(200);
    }

    public function test_profile_show_returns_404_for_soft_deleted_profile(): void
    {
        $user = $this->createApprovedProvider(['slug' => 'deleted-profile']);

        ProviderProfile::query()->where('user_id', $user->id)->first()->delete();

        $response = $this->get(route('profile.show', ['slug' => 'deleted-profile']));

        $response->assertStatus(404);
    }

    // ---------------------------------------------------------------
    // Profile data integrity
    // ---------------------------------------------------------------

    public function test_profile_view_data_contains_correct_name_and_age(): void
    {
        $this->createApprovedProvider(['name' => 'Jade', 'age' => 24]);

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $profile = $response->viewData('profile');
        $this->assertSame('Jade', $profile['name']);
        $this->assertSame(24, $profile['age']);
    }

    public function test_profile_view_data_contains_correct_slug(): void
    {
        $this->createApprovedProvider();

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $profile = $response->viewData('profile');
        $this->assertSame('jade010-10', $profile['slug']);
    }

    public function test_profile_view_data_defaults_rate_to_contact_for_rate_when_no_rates(): void
    {
        $this->createApprovedProvider();

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $profile = $response->viewData('profile');
        $this->assertSame('Contact for rate', $profile['rate']);
    }

    public function test_profile_view_data_shows_incall_rate_when_set(): void
    {
        $user = $this->createApprovedProvider();

        Rate::query()->create([
            'user_id' => $user->id,
            'description' => '1 hour',
            'incall' => '$300',
            'outcall' => '$350',
        ]);

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $profile = $response->viewData('profile');
        $this->assertSame('$300', $profile['rate']);
    }

    public function test_profile_view_data_falls_back_to_outcall_when_incall_is_empty(): void
    {
        $user = $this->createApprovedProvider();

        Rate::query()->create([
            'user_id'     => $user->id,
            'description' => '1 hour',
            'incall'      => '',
            'outcall'     => '$350',
        ]);

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $profile = $response->viewData('profile');
        $this->assertSame('$350', $profile['rate']);
    }

    public function test_profile_view_data_price_list_contains_all_rates(): void
    {
        $user = $this->createApprovedProvider();

        Rate::query()->create(['user_id' => $user->id, 'description' => '30 min', 'incall' => '$150', 'outcall' => '$180']);
        Rate::query()->create(['user_id' => $user->id, 'description' => '1 hour', 'incall' => '$250', 'outcall' => '$300']);

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $profile = $response->viewData('profile');
        $this->assertCount(2, $profile['price_list']);
    }

    public function test_profile_stats_array_contains_expected_labels(): void
    {
        $this->createApprovedProvider();

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $profileStats = $response->viewData('profileStats');
        $labels = array_column($profileStats, 'label');

        $this->assertContains('Age group', $labels);
        $this->assertContains('Ethnicity', $labels);
        $this->assertContains('Hair color', $labels);
        $this->assertContains('Hair length', $labels);
        $this->assertContains('Body type', $labels);
        $this->assertContains('Bust size', $labels);
        $this->assertContains('Length', $labels);
    }

    public function test_profile_stats_show_dash_when_category_not_set(): void
    {
        $this->createApprovedProvider(['age_group_id' => null]);

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $profileStats = $response->viewData('profileStats');
        $ageGroupStat = collect($profileStats)->firstWhere('label', 'Age group');

        $this->assertSame('—', $ageGroupStat['value']);
    }

    public function test_profile_is_verified_flag_is_present_in_view_data(): void
    {
        $this->createApprovedProvider(['is_verified' => true]);

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $profile = $response->viewData('profile');
        $this->assertTrue($profile['is_verified']);
    }

    public function test_profile_is_featured_flag_is_present_in_view_data(): void
    {
        $this->createApprovedProvider(['is_featured' => true]);

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $profile = $response->viewData('profile');
        $this->assertTrue($profile['is_featured']);
    }

    // ---------------------------------------------------------------
    // Adjacent profile navigation
    // ---------------------------------------------------------------

    public function test_prev_and_next_profile_are_arrays_with_slug_and_name_keys(): void
    {
        $this->createApprovedProvider();

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $prevProfile = $response->viewData('prevProfile');
        $nextProfile = $response->viewData('nextProfile');

        $this->assertArrayHasKey('slug', $prevProfile);
        $this->assertArrayHasKey('name', $prevProfile);
        $this->assertArrayHasKey('slug', $nextProfile);
        $this->assertArrayHasKey('name', $nextProfile);
    }

    public function test_prev_and_next_profile_wrap_around_to_same_profile_when_only_one_exists(): void
    {
        $this->createApprovedProvider(['name' => 'Jade', 'slug' => 'jade010-10']);

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $prevProfile = $response->viewData('prevProfile');
        $nextProfile = $response->viewData('nextProfile');

        // With only one approved profile the adjacent queries wrap back to the same profile.
        $this->assertSame('jade010-10', $prevProfile['slug']);
        $this->assertSame('jade010-10', $nextProfile['slug']);
    }

    public function test_prev_and_next_navigation_return_correct_adjacent_profiles(): void
    {
        $this->createApprovedProvider(['name' => 'Alice', 'slug' => 'alice-001']);
        $this->createApprovedProvider(['name' => 'Jade', 'slug' => 'jade010-10']);
        $this->createApprovedProvider(['name' => 'Bella', 'slug' => 'bella-001']);

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $prevProfile = $response->viewData('prevProfile');
        $nextProfile = $response->viewData('nextProfile');

        $this->assertSame('Alice', $prevProfile['name']);
        $this->assertSame('Bella', $nextProfile['name']);
    }

    // ---------------------------------------------------------------
    // Nearby profiles
    // ---------------------------------------------------------------

    public function test_nearby_profiles_is_an_array(): void
    {
        $this->createApprovedProvider();

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $this->assertIsArray($response->viewData('nearbyProfiles'));
    }

    public function test_nearby_profiles_excludes_current_profile(): void
    {
        $this->createApprovedProvider(['name' => 'Jade', 'slug' => 'jade010-10']);
        $this->createApprovedProvider(['name' => 'Ruby', 'slug' => 'ruby-001']);

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $nearbyNames = array_column($response->viewData('nearbyProfiles'), 'name');
        $this->assertNotContains('Jade', $nearbyNames);
        $this->assertContains('Ruby', $nearbyNames);
    }

    public function test_nearby_profiles_limited_to_four_results(): void
    {
        $this->createApprovedProvider(['slug' => 'jade010-10']);

        for ($i = 1; $i <= 6; $i++) {
            $this->createApprovedProvider(['name' => "Escort {$i}", 'slug' => "escort-{$i}"]);
        }

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $this->assertLessThanOrEqual(4, count($response->viewData('nearbyProfiles')));
    }

    public function test_each_nearby_profile_has_expected_keys(): void
    {
        $this->createApprovedProvider(['slug' => 'jade010-10']);
        $this->createApprovedProvider(['name' => 'Ruby', 'slug' => 'ruby-001']);

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $nearby = $response->viewData('nearbyProfiles');
        $this->assertNotEmpty($nearby, 'Expected at least one nearby profile to be returned');

        $this->assertArrayHasKey('slug', $nearby[0]);
        $this->assertArrayHasKey('name', $nearby[0]);
        $this->assertArrayHasKey('image', $nearby[0]);
        $this->assertArrayHasKey('city', $nearby[0]);
        $this->assertArrayHasKey('rate', $nearby[0]);
    }

    // ---------------------------------------------------------------
    // Category filter parameter (selectedCategoryNames)
    // ---------------------------------------------------------------

    public function test_selected_category_names_is_empty_when_no_categories_passed(): void
    {
        $this->createApprovedProvider();

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $this->assertEmpty($response->viewData('selectedCategoryNames'));
    }

    public function test_selected_category_names_populated_when_valid_category_ids_passed(): void
    {
        $this->createApprovedProvider();

        $category = Category::query()->create([
            'name'         => 'Brunette',
            'slug'         => 'brunette',
            'website_type' => 'adult',
            'is_active'    => true,
        ]);

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10', 'categories' => [$category->id]]));

        $this->assertContains('Brunette', $response->viewData('selectedCategoryNames'));
    }

    public function test_profile_show_rejects_invalid_category_id_with_validation_error(): void
    {
        $this->createApprovedProvider();

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10', 'categories' => [99999]]));

        $response->assertSessionHasErrors(['categories.0']);
    }

    public function test_profile_show_rejects_inactive_category_id(): void
    {
        $this->createApprovedProvider();

        $category = Category::query()->create([
            'name'         => 'Inactive Cat',
            'slug'         => 'inactive-cat',
            'website_type' => 'adult',
            'is_active'    => false,
        ]);

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10', 'categories' => [$category->id]]));

        $response->assertSessionHasErrors(['categories.0']);
    }

    // ---------------------------------------------------------------
    // Site password protection
    // ---------------------------------------------------------------

    public function test_profile_show_redirects_to_site_password_when_protection_enabled(): void
    {
        $this->createApprovedProvider();

        SiteSetting::query()->create([
            'site_password'         => 'secret123',
            'site_password_enabled' => true,
        ]);

        $response = $this->get(route('profile.show', ['slug' => 'jade010-10']));

        $response->assertRedirect('/site-password');
    }

    public function test_profile_show_accessible_when_site_password_session_is_set(): void
    {
        $this->createApprovedProvider();

        SiteSetting::query()->create([
            'site_password'         => 'secret123',
            'site_password_enabled' => true,
        ]);

        $response = $this->withSession(['site_access' => true])
            ->get(route('profile.show', ['slug' => 'jade010-10']));

        $response->assertStatus(200);
    }

    public function test_admin_user_bypasses_site_password_on_profile_show(): void
    {
        $this->createApprovedProvider();

        SiteSetting::query()->create([
            'site_password'         => 'secret123',
            'site_password_enabled' => true,
        ]);

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)
            ->get(route('profile.show', ['slug' => 'jade010-10']));

        $response->assertStatus(200);
    }
}
