<?php

namespace Tests\Feature\Profile;

use App\Actions\GetMyProfilePageData;
use App\Actions\GetMyProfileStepTwoData;
use App\Actions\SaveMyProfile;
use App\Actions\Support\ActionResult;
use App\Models\Category;
use App\Models\ProviderProfile;
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
}
