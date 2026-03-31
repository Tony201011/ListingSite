<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckProfileSteps;
use App\Models\ProfileImage;
use App\Models\ProviderProfile;
use App\Models\Rate;
use App\Models\RateGroup;
use App\Models\Tour;
use App\Models\User;
use App\Models\UserVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function providerWithProfile(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        ProviderProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'test-' . $user->id,
        ]);

        return $user;
    }

    private function userWithoutProfile(): User
    {
        return User::factory()->create(['role' => User::ROLE_PROVIDER]);
    }

    // ---------------------------------------------------------------
    // 1. Guest access — write endpoints reject unauthenticated users
    // ---------------------------------------------------------------

    public function test_guest_cannot_upload_photos(): void
    {
        $response = $this->postJson(route('photos.upload'), ['photos' => []]);
        $response->assertUnauthorized();
    }

    public function test_guest_cannot_set_cover_photo(): void
    {
        $user = User::factory()->create();
        $photo = ProfileImage::create([
            'user_id' => $user->id,
            'image_path' => 'images/photo.jpg',
            'thumbnail_path' => 'thumbnails/photo.jpg',
            'is_primary' => false,
        ]);

        $response = $this->postJson(route('photos.setCover', $photo));
        $response->assertUnauthorized();
    }

    public function test_guest_cannot_delete_photo(): void
    {
        $user = User::factory()->create();
        $photo = ProfileImage::create([
            'user_id' => $user->id,
            'image_path' => 'images/photo.jpg',
            'thumbnail_path' => 'thumbnails/photo.jpg',
            'is_primary' => false,
        ]);

        $response = $this->deleteJson(route('photos.destroy', $photo));
        $response->assertUnauthorized();
    }

    public function test_guest_cannot_update_profile(): void
    {
        $response = $this->postJson(route('edit-profile.save'), []);
        $response->assertUnauthorized();
    }

    public function test_guest_cannot_delete_account(): void
    {
        $response = $this->deleteJson(route('account.destroy'), []);
        $response->assertUnauthorized();
    }

    // ---------------------------------------------------------------
    // 2. Cross-user — cannot modify another user's resources
    // ---------------------------------------------------------------

    public function test_user_cannot_set_cover_on_another_users_photo(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();

        $photo = ProfileImage::create([
            'user_id' => $owner->id,
            'image_path' => 'images/owner-photo.jpg',
            'thumbnail_path' => 'thumbnails/owner-photo.jpg',
            'is_primary' => false,
        ]);

        $response = $this->actingAs($attacker)->postJson(route('photos.setCover', $photo));
        $response->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_photo(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();

        $photo = ProfileImage::create([
            'user_id' => $owner->id,
            'image_path' => 'images/owner-photo.jpg',
            'thumbnail_path' => 'thumbnails/owner-photo.jpg',
            'is_primary' => false,
        ]);

        $response = $this->actingAs($attacker)->deleteJson(route('photos.destroy', $photo));
        $response->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_video(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();

        $video = UserVideo::create([
            'user_id' => $owner->id,
            'video_path' => 'videos/owner-video.mp4',
            'original_name' => 'owner-video.mp4',
        ]);

        $response = $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($attacker)
            ->deleteJson(route('videos.destroy', $video));
        $response->assertForbidden();
    }

    public function test_user_cannot_update_another_users_rate(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();

        $rate = Rate::create([
            'user_id' => $owner->id,
            'description' => '30 min',
            'incall' => 200,
            'outcall' => 250,
        ]);

        $response = $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($attacker)
            ->putJson(route('my-rate.update', $rate), [
                'description' => 'Hacked',
                'incall' => '999',
                'outcall' => '999',
            ]);
        $response->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_rate(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();

        $rate = Rate::create([
            'user_id' => $owner->id,
            'description' => '30 min',
            'incall' => 200,
            'outcall' => 250,
        ]);

        $response = $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($attacker)
            ->deleteJson(route('my-rate.destroy', $rate));
        $response->assertForbidden();
    }

    public function test_user_cannot_update_another_users_rate_group(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();

        $group = RateGroup::create([
            'user_id' => $owner->id,
            'name' => 'Standard',
        ]);

        $response = $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($attacker)
            ->putJson(route('my-rate.groups.update', $group), [
                'name' => 'Hacked',
            ]);
        $response->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_rate_group(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();

        $group = RateGroup::create([
            'user_id' => $owner->id,
            'name' => 'Standard',
        ]);

        $response = $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($attacker)
            ->deleteJson(route('my-rate.groups.destroy', $group));
        $response->assertForbidden();
    }

    public function test_user_cannot_update_another_users_tour(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();

        $tour = Tour::create([
            'user_id' => $owner->id,
            'city' => 'Sydney',
            'from' => now()->addDays(1),
            'to' => now()->addDays(5),
        ]);

        $response = $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($attacker)
            ->putJson(route('my-tours.update', $tour), [
                'city' => 'Hacked',
                'from' => now()->addDays(1)->toDateString(),
                'to' => now()->addDays(5)->toDateString(),
            ]);
        $response->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_tour(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();

        $tour = Tour::create([
            'user_id' => $owner->id,
            'city' => 'Sydney',
            'from' => now()->addDays(1),
            'to' => now()->addDays(5),
        ]);

        $response = $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($attacker)
            ->deleteJson(route('my-tours.destroy', $tour));
        $response->assertForbidden();
    }

    // ---------------------------------------------------------------
    // 3. Policy unit tests — verify policy logic directly
    // ---------------------------------------------------------------

    public function test_provider_profile_policy_blocks_update_without_profile(): void
    {
        $user = $this->userWithoutProfile();

        $this->assertFalse(
            app(\App\Policies\ProviderProfilePolicy::class)->update($user)
        );
    }

    public function test_provider_profile_policy_allows_update_with_profile(): void
    {
        $user = $this->providerWithProfile();

        $this->assertTrue(
            app(\App\Policies\ProviderProfilePolicy::class)->update($user)
        );
    }

    public function test_profile_image_policy_blocks_cross_user_delete(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();

        $photo = ProfileImage::create([
            'user_id' => $owner->id,
            'image_path' => 'images/photo.jpg',
            'thumbnail_path' => 'thumbnails/photo.jpg',
            'is_primary' => false,
        ]);

        $this->assertFalse(
            app(\App\Policies\ProfileImagePolicy::class)->delete($attacker, $photo)
        );
    }

    public function test_tour_policy_blocks_cross_user_update(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();

        $tour = Tour::create([
            'user_id' => $owner->id,
            'city' => 'Sydney',
            'from' => now()->addDays(1),
            'to' => now()->addDays(5),
        ]);

        $this->assertFalse(
            app(\App\Policies\TourPolicy::class)->update($attacker, $tour)
        );
    }

    public function test_rate_policy_blocks_cross_user_delete(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();

        $rate = Rate::create([
            'user_id' => $owner->id,
            'description' => '30 min',
            'incall' => '200',
            'outcall' => '250',
        ]);

        $this->assertFalse(
            app(\App\Policies\RatePolicy::class)->delete($attacker, $rate)
        );
    }
}
