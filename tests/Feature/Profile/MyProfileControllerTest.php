<?php

namespace Tests\Feature\Profile;

use App\Actions\GetMyProfilePageData;
use App\Actions\GetMyProfileStepTwoData;
use App\Actions\SaveMyProfile;
use App\Actions\Support\ActionResult;
use App\Models\Category;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class MyProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

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

    /**
     * Act as a provider with their first profile already selected in session.
     */
    private function actingAsProvider(User $user): static
    {
        $profile = $user->providerProfile;

        return $this->actingAs($user)->withSession([
            'active_provider_profile_id' => $profile?->id,
        ]);
    }

    /**
     * Create a parent + child category for a given type slug.
     * Returns the child category ID to use in form submissions.
     */
    private function createCategoryForType(string $parentSlug): int
    {
        $parent = Category::query()->create([
            'name' => $parentSlug,
            'slug' => $parentSlug,
            'website_type' => 'adult',
            'is_active' => true,
        ]);

        $child = Category::query()->create([
            'parent_id' => $parent->id,
            'name' => 'Option',
            'slug' => $parentSlug.'-option',
            'website_type' => 'adult',
            'is_active' => true,
        ]);

        return $child->id;
    }

    public function test_my_profile_view_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider();
        SiteSetting::query()->create([
            'online_status_max_uses' => 6,
            'online_status_duration_minutes' => 90,
        ]);

        $getMyProfilePageData = Mockery::mock(GetMyProfilePageData::class);
        $getMyProfilePageData->shouldReceive('execute')
            ->once()
            ->andReturn([
                'user' => $user,
                'profile' => $user->providerProfile,
                'stepOneCompleted' => false,
                'stepTwoCompleted' => false,
                'stepPhotoVerificationCompleted' => false,
                'profileUrl' => null,
                'shortUrlFull' => null,
                'babeRank' => 0,
            ]);

        $this->app->instance(GetMyProfilePageData::class, $getMyProfilePageData);

        $response = $this->actingAsProvider($user)->get(route('my-profile'));

        $response->assertOk();
        $response->assertViewIs('profile.my-profile-1');
        $response->assertSeeText('Use this feature up to 6 times a day for 01:30:00.');
    }

    public function test_my_profile_view_passes_page_data_from_action(): void
    {
        $user = $this->createProvider();

        $getMyProfilePageData = Mockery::mock(GetMyProfilePageData::class);
        $getMyProfilePageData->shouldReceive('execute')
            ->once()
            ->andReturn([
                'user' => $user,
                'profile' => $user->providerProfile,
                'stepOneCompleted' => true,
                'stepTwoCompleted' => true,
                'stepPhotoVerificationCompleted' => false,
                'profileUrl' => 'http://example.com/profile/provider-1',
                'shortUrlFull' => null,
                'babeRank' => 42,
            ]);

        $this->app->instance(GetMyProfilePageData::class, $getMyProfilePageData);

        $response = $this->actingAsProvider($user)->get(route('my-profile'));

        $response->assertViewHas('stepOneCompleted', true);
        $response->assertViewHas('stepTwoCompleted', true);
    }

    public function test_my_profile_view_shows_listing_boost_status_defaults_and_purchase_entry_point(): void
    {
        $user = $this->createProvider();

        $getMyProfilePageData = Mockery::mock(GetMyProfilePageData::class);
        $getMyProfilePageData->shouldReceive('execute')
            ->once()
            ->andReturn([
                'user' => $user,
                'profile' => $user->providerProfile,
                'stepOneCompleted' => true,
                'stepTwoCompleted' => true,
                'stepPhotoVerificationCompleted' => false,
                'profileUrl' => null,
                'shortUrlFull' => null,
                'babeRank' => 42,
                'listingBoostStatuses' => [
                    ['label' => 'Featured Expires', 'value' => 'Never / Not set'],
                    ['label' => 'Free Listing Until', 'value' => 'Expired / Not set'],
                    ['label' => 'Home Page Featured Until', 'value' => 'Not active'],
                    ['label' => 'Local Banner Until', 'value' => 'Not active'],
                    ['label' => 'Home Banner Until', 'value' => 'Not active'],
                ],
            ]);

        $this->app->instance(GetMyProfilePageData::class, $getMyProfilePageData);

        $response = $this->actingAsProvider($user)->get(route('my-profile'));

        $response->assertOk();
        $response->assertSee('LISTING BOOST STATUS');
        $response->assertSee('Featured Expires');
        $response->assertSee('Never / Not set');
        $response->assertSee('Free Listing Until');
        $response->assertSee('Expired / Not set');
        $response->assertSee('Home Page Featured Until');
        $response->assertSee('Local Banner Until');
        $response->assertSee('Home Banner Until');
        $response->assertSee('Not active');
        $response->assertSee(route('featured'));
    }

    public function test_edit_profile_view_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider();

        $getMyProfileStepTwoData = Mockery::mock(GetMyProfileStepTwoData::class);
        $getMyProfileStepTwoData->shouldReceive('execute')
            ->once()
            ->andReturn([
                'user' => $user,
                'profile' => $user->providerProfile,
                'selected' => [],
                'ageGroupOptions' => collect(),
                'hairColorOptions' => collect(),
                'hairLengthOptions' => collect(),
                'ethnicityOptions' => collect(),
                'bodyTypeOptions' => collect(),
                'bustSizeOptions' => collect(),
                'yourLengthOptions' => collect(),
                'primaryTags' => collect(),
                'attrTags' => collect(),
                'styleTags' => collect(),
                'services' => collect(),
                'availabilityOptions' => collect(),
                'contactMethodOptions' => collect(),
                'phoneContactOptions' => collect(),
                'timeWasterOptions' => collect(),
                'contactEmail' => 'contact@example.com',
            ]);

        $this->app->instance(GetMyProfileStepTwoData::class, $getMyProfileStepTwoData);

        $response = $this->actingAsProvider($user)->get(route('edit-profile'));

        $response->assertOk();
        $response->assertViewIs('profile.my-profile-2');
    }

    public function test_save_calls_action_and_returns_json_on_json_request(): void
    {
        $user = $this->createProvider();

        $ageGroupId = $this->createCategoryForType('age-group');
        $hairColorId = $this->createCategoryForType('hair-color');
        $hairLengthId = $this->createCategoryForType('hair-length');
        $ethnicityId = $this->createCategoryForType('ethnicity');
        $bodyTypeId = $this->createCategoryForType('body-type');
        $bustSizeId = $this->createCategoryForType('bust-size');
        $yourLengthId = $this->createCategoryForType('your-length');

        $saveMyProfile = Mockery::mock(SaveMyProfile::class);
        $saveMyProfile->shouldReceive('execute')
            ->once()
            ->andReturn(ActionResult::success([], 'Profile updated successfully.'));

        $this->app->instance(SaveMyProfile::class, $saveMyProfile);

        $response = $this->actingAsProvider($user)->postJson(route('edit-profile.save'), [
            'name' => 'Jenny',
            'suburb' => 'Sydney',
            'introduction_line' => 'Hello there',
            'profile_text' => 'My profile text',
            'age_group' => $ageGroupId,
            'hair_color' => $hairColorId,
            'hair_length' => $hairLengthId,
            'ethnicity' => $ethnicityId,
            'body_type' => $bodyTypeId,
            'bust_size' => $bustSizeId,
            'your_length' => $yourLengthId,
            'availability' => 'Weekdays',
            'contact_method' => 'Phone',
            'phone_contact' => 'Call',
            'time_waster' => 'Basic',
            'primary_identity' => ['Option A'],
            'attributes' => ['Attr A'],
            'services_style' => ['Style A'],
            'services_provided' => ['Service A'],
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Profile updated successfully.',
        ]);
    }

    public function test_save_returns_error_when_action_fails(): void
    {
        $user = $this->createProvider();

        $ageGroupId = $this->createCategoryForType('age-group');
        $hairColorId = $this->createCategoryForType('hair-color');
        $hairLengthId = $this->createCategoryForType('hair-length');
        $ethnicityId = $this->createCategoryForType('ethnicity');
        $bodyTypeId = $this->createCategoryForType('body-type');
        $bustSizeId = $this->createCategoryForType('bust-size');
        $yourLengthId = $this->createCategoryForType('your-length');

        $saveMyProfile = Mockery::mock(SaveMyProfile::class);
        $saveMyProfile->shouldReceive('execute')
            ->once()
            ->andReturn(ActionResult::authorizationFailure('Forbidden'));

        $this->app->instance(SaveMyProfile::class, $saveMyProfile);

        $response = $this->actingAsProvider($user)->postJson(route('edit-profile.save'), [
            'name' => 'Jenny',
            'suburb' => 'Sydney',
            'introduction_line' => 'Hello there',
            'profile_text' => 'My profile text',
            'age_group' => $ageGroupId,
            'hair_color' => $hairColorId,
            'hair_length' => $hairLengthId,
            'ethnicity' => $ethnicityId,
            'body_type' => $bodyTypeId,
            'bust_size' => $bustSizeId,
            'your_length' => $yourLengthId,
            'availability' => 'Weekdays',
            'contact_method' => 'Phone',
            'phone_contact' => 'Call',
            'time_waster' => 'Basic',
            'primary_identity' => ['Option A'],
            'attributes' => ['Attr A'],
            'services_style' => ['Style A'],
            'services_provided' => ['Service A'],
        ]);

        $response->assertStatus(403);
    }

    public function test_save_returns_422_when_required_fields_are_missing(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAsProvider($user)->postJson(route('edit-profile.save'), []);

        $response->assertStatus(422);
    }

    public function test_guest_cannot_access_my_profile(): void
    {
        $response = $this->get(route('my-profile'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_access_edit_profile(): void
    {
        $response = $this->get(route('edit-profile'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_save_profile(): void
    {
        $response = $this->postJson(route('edit-profile.save'), []);

        $response->assertStatus(401);
    }

    /**
     * Helper to create a named category used for select-by-name fields
     * (availability, contact-method, phone-contact-preferences, time-waster-shield).
     * Creates the parent category if it does not exist, then creates a child with the given name.
     */
    private function createCategoryByName(string $parentSlug, string $childName): void
    {
        $parent = Category::query()->firstOrCreate(
            ['slug' => $parentSlug, 'website_type' => 'adult'],
            ['name' => $parentSlug, 'is_active' => true]
        );

        Category::query()->firstOrCreate(
            ['parent_id' => $parent->id, 'name' => $childName, 'website_type' => 'adult'],
            ['slug' => $parentSlug.'-'.str_replace(' ', '-', strtolower($childName)), 'is_active' => true]
        );
    }

    /**
     * Build a valid full profile payload for SaveMyProfile integration tests.
     */
    private function buildValidProfilePayload(array $overrides = []): array
    {
        $ageGroupId = $this->createCategoryForType('age-group');
        $hairColorId = $this->createCategoryForType('hair-color');
        $hairLengthId = $this->createCategoryForType('hair-length');
        $ethnicityId = $this->createCategoryForType('ethnicity');
        $bodyTypeId = $this->createCategoryForType('body-type');
        $bustSizeId = $this->createCategoryForType('bust-size');
        $yourLengthId = $this->createCategoryForType('your-length');

        $this->createCategoryByName('primary-identity', 'Identity A');
        $this->createCategoryByName('attributes', 'Attr A');
        $this->createCategoryByName('services-style', 'Style A');
        $this->createCategoryByName('services-you-provide', 'Service A');
        $this->createCategoryByName('availability', 'Weekdays');
        $this->createCategoryByName('contact-method', 'Phone');
        $this->createCategoryByName('phone-contact-preferences', 'Call');
        $this->createCategoryByName('time-waster-shield', 'Basic');

        return array_merge([
            'name' => 'Jenny',
            'suburb' => 'Sydney',
            'introduction_line' => 'Hello there',
            'profile_text' => 'My profile text',
            'age_group' => $ageGroupId,
            'hair_color' => $hairColorId,
            'hair_length' => $hairLengthId,
            'ethnicity' => $ethnicityId,
            'body_type' => $bodyTypeId,
            'bust_size' => $bustSizeId,
            'your_length' => $yourLengthId,
            'availability' => 'Weekdays',
            'contact_method' => 'Phone',
            'phone_contact' => 'Call',
            'time_waster' => 'Basic',
            'primary_identity' => ['Identity A'],
            'attributes' => ['Attr A'],
            'services_style' => ['Style A'],
            'services_provided' => ['Service A'],
        ], $overrides);
    }

    public function test_save_syncs_mobile_to_users_table_when_phone_provided(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'mobile' => '0400000000',
            'mobile_verified' => true,
        ]);

        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
        ]);

        $payload = $this->buildValidProfilePayload(['phone' => '0499999999']);

        $response = $this->actingAsProvider($user)->postJson(route('edit-profile.save'), $payload);

        $response->assertOk();

        $user->refresh();
        $this->assertSame('0499999999', $user->mobile);
        $this->assertFalse($user->mobile_verified);
    }

    public function test_save_resets_mobile_verified_when_mobile_changes(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'mobile' => '0400000000',
            'mobile_verified' => true,
        ]);

        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
        ]);

        $payload = $this->buildValidProfilePayload(['phone' => '0411111111']);

        $this->actingAsProvider($user)->postJson(route('edit-profile.save'), $payload);

        $user->refresh();
        $this->assertFalse($user->mobile_verified, 'mobile_verified should be reset when mobile changes');
    }

    public function test_save_preserves_mobile_verified_when_mobile_unchanged(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'mobile' => '0400000000',
            'mobile_verified' => true,
        ]);

        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
        ]);

        // Submit with the same phone number as the existing mobile
        $payload = $this->buildValidProfilePayload(['phone' => '0400000000']);

        $this->actingAsProvider($user)->postJson(route('edit-profile.save'), $payload);

        $user->refresh();
        $this->assertTrue($user->mobile_verified, 'mobile_verified should stay true when mobile is unchanged');
    }

    public function test_save_does_not_update_mobile_when_phone_not_provided(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'mobile' => '0400000000',
            'mobile_verified' => true,
        ]);

        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
        ]);

        // No phone field in payload
        $payload = $this->buildValidProfilePayload();
        unset($payload['phone']);

        $this->actingAsProvider($user)->postJson(route('edit-profile.save'), $payload);

        $user->refresh();
        $this->assertSame('0400000000', $user->mobile);
        $this->assertTrue($user->mobile_verified);
    }
}
