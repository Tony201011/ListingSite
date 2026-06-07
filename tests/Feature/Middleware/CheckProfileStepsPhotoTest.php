<?php

namespace Tests\Feature\Middleware;

use App\Models\Category;
use App\Models\ProfileImage;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckProfileStepsPhotoTest extends TestCase
{
    use RefreshDatabase;

    private function createProviderWithProfile(): array
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
        ]);

        session(['active_provider_profile_id' => $profile->id]);

        return [$user, $profile];
    }

    private function completeProfileStepOne(ProviderProfile $profile): void
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'test-category'],
            ['name' => 'Test Category']
        );

        $profile->update([
            'introduction_line' => 'Intro',
            'profile_text' => 'Complete profile text',
            'age_group_id' => $category->id,
            'hair_color_id' => $category->id,
            'hair_length_id' => $category->id,
            'ethnicity_id' => $category->id,
            'body_type_id' => $category->id,
            'bust_size_id' => $category->id,
            'your_length_id' => $category->id,
            'availability' => 'available',
            'contact_method' => 'phone',
            'phone_contact_preference' => 'call',
            'time_waster_shield' => 'enabled',
            'primary_identity' => [1],
            'attributes' => [1],
            'services_style' => [1],
            'services_provided' => [1],
        ]);
    }

    public function test_online_now_page_is_blocked_when_active_profile_has_no_photos(): void
    {
        [$user, $profile] = $this->createProviderWithProfile();
        $this->completeProfileStepOne($profile);

        $response = $this->actingAs($user)->get(route('online-now'));

        $response->assertRedirect(route('my-profile'));
        $response->assertSessionHas('error', 'Please upload at least one photo.');
    }

    public function test_online_status_update_is_blocked_when_active_profile_has_no_photos(): void
    {
        [$user, $profile] = $this->createProviderWithProfile();
        $this->completeProfileStepOne($profile);

        $response = $this->actingAs($user)->postJson(route('online.update-status'), [
            'status' => 'online',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Please upload at least one photo.']);
    }

    public function test_online_status_update_is_blocked_when_user_has_photos_on_different_profile_only(): void
    {
        [$user, $activeProfile] = $this->createProviderWithProfile();
        $this->completeProfileStepOne($activeProfile);

        // Create another profile with photos — the active profile still has none
        $otherProfile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name.' 2',
            'slug' => 'provider-'.$user->id.'-2',
        ]);

        ProfileImage::factory()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $otherProfile->id,
        ]);

        // Ensure the active profile is the one without photos
        session(['active_provider_profile_id' => $activeProfile->id]);

        $response = $this->actingAs($user)->postJson(route('online.update-status'), [
            'status' => 'online',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Please upload at least one photo.']);
    }

    public function test_online_status_update_is_allowed_when_active_profile_has_its_own_photo(): void
    {
        [$user, $profile] = $this->createProviderWithProfile();
        $this->completeProfileStepOne($profile);

        ProfileImage::factory()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
        ]);

        $response = $this->actingAs($user)->postJson(route('online.update-status'), [
            'status' => 'online',
        ]);

        // Should not be blocked by middleware (200 or domain-level response)
        $response->assertStatus(200);
    }
}
