<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckProfileSteps;
use App\Models\PhotoVerification;
use App\Models\ProfileImage;
use App\Models\ProviderProfile;
use App\Models\Rate;
use App\Models\RateGroup;
use App\Models\ShortUrl;
use App\Models\Tour;
use App\Models\User;
use App\Models\UserVideo;
use Illuminate\Http\UploadedFile;
use App\Policies\PhotoVerificationPolicy;
use App\Policies\ProfileImagePolicy;
use App\Policies\ProviderProfilePolicy;
use App\Policies\RateGroupPolicy;
use App\Policies\RatePolicy;
use App\Policies\ShortUrlPolicy;
use App\Policies\TourPolicy;
use App\Policies\UserVideoPolicy;
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

    private function makePhoto(User $owner, bool $primary = false): ProfileImage
    {
        return ProfileImage::create([
            'user_id' => $owner->id,
            'image_path' => 'images/photo-' . $owner->id . '.jpg',
            'thumbnail_path' => 'thumbnails/photo-' . $owner->id . '.jpg',
            'is_primary' => $primary,
        ]);
    }

    private function makeVideo(User $owner): UserVideo
    {
        return UserVideo::create([
            'user_id' => $owner->id,
            'video_path' => 'videos/video-' . $owner->id . '.mp4',
            'original_name' => 'video.mp4',
        ]);
    }

    private function makeRate(User $owner): Rate
    {
        return Rate::create([
            'user_id' => $owner->id,
            'description' => '30 min',
            'incall' => 200,
            'outcall' => 250,
        ]);
    }

    private function makeRateGroup(User $owner): RateGroup
    {
        return RateGroup::create([
            'user_id' => $owner->id,
            'name' => 'Standard',
        ]);
    }

    private function makeTour(User $owner): Tour
    {
        return Tour::create([
            'user_id' => $owner->id,
            'city' => 'Sydney',
            'from' => now()->addDays(1),
            'to' => now()->addDays(5),
        ]);
    }

    private function makeShortUrl(User $owner): ShortUrl
    {
        return ShortUrl::create([
            'user_id' => $owner->id,
            'short_url' => 'slug-' . $owner->id,
        ]);
    }

    private function makePhotoVerification(User $owner): PhotoVerification
    {
        return PhotoVerification::create([
            'user_id' => $owner->id,
            'photos' => [['url' => 'verifications/photo.jpg']],
            'status' => 'pending',
        ]);
    }

    // ===============================================================
    // 1. Guest access — unauthenticated users rejected
    // ===============================================================

    // --- Photos ---

    public function test_guest_cannot_upload_photos(): void
    {
        $this->postJson(route('photos.upload'), ['photos' => []])->assertUnauthorized();
    }

    public function test_guest_cannot_set_cover_photo(): void
    {
        $photo = $this->makePhoto(User::factory()->create());

        $this->postJson(route('photos.setCover', $photo))->assertUnauthorized();
    }

    public function test_guest_cannot_delete_photo(): void
    {
        $photo = $this->makePhoto(User::factory()->create());

        $this->deleteJson(route('photos.destroy', $photo))->assertUnauthorized();
    }

    // --- Videos ---

    public function test_guest_cannot_delete_video(): void
    {
        $video = $this->makeVideo(User::factory()->create());

        $this->deleteJson(route('videos.destroy', $video))->assertUnauthorized();
    }

    // --- Tours ---

    public function test_guest_cannot_store_tour(): void
    {
        $this->postJson(route('my-tours.store'), [])->assertUnauthorized();
    }

    public function test_guest_cannot_update_tour(): void
    {
        $tour = $this->makeTour(User::factory()->create());

        $this->putJson(route('my-tours.update', $tour), [])->assertUnauthorized();
    }

    public function test_guest_cannot_delete_tour(): void
    {
        $tour = $this->makeTour(User::factory()->create());

        $this->deleteJson(route('my-tours.destroy', $tour))->assertUnauthorized();
    }

    // --- Rates ---

    public function test_guest_cannot_store_rate(): void
    {
        $this->postJson(route('my-rate.store'), [])->assertUnauthorized();
    }

    public function test_guest_cannot_update_rate(): void
    {
        $rate = $this->makeRate(User::factory()->create());

        $this->putJson(route('my-rate.update', $rate), [])->assertUnauthorized();
    }

    public function test_guest_cannot_delete_rate(): void
    {
        $rate = $this->makeRate(User::factory()->create());

        $this->deleteJson(route('my-rate.destroy', $rate))->assertUnauthorized();
    }

    // --- Rate Groups ---

    public function test_guest_cannot_update_rate_group(): void
    {
        $group = $this->makeRateGroup(User::factory()->create());

        $this->putJson(route('my-rate.groups.update', $group), [])->assertUnauthorized();
    }

    public function test_guest_cannot_delete_rate_group(): void
    {
        $group = $this->makeRateGroup(User::factory()->create());

        $this->deleteJson(route('my-rate.groups.destroy', $group))->assertUnauthorized();
    }

    // --- Profile ---

    public function test_guest_cannot_update_profile(): void
    {
        $this->postJson(route('edit-profile.save'), [])->assertUnauthorized();
    }

    public function test_guest_cannot_delete_account(): void
    {
        $this->deleteJson(route('account.destroy'), [])->assertUnauthorized();
    }

    // --- Photo Verification ---

    public function test_guest_cannot_upload_verification_photo(): void
    {
        $this->postJson(route('photo-verification.upload'), [])->assertUnauthorized();
    }

    public function test_guest_cannot_delete_verification_photo(): void
    {
        $this->postJson(route('photo-verification.delete-photo'), [])->assertUnauthorized();
    }

    // --- Short URL ---

    public function test_guest_cannot_update_short_url(): void
    {
        $this->postJson(route('short-url.update'), [])->assertUnauthorized();
    }

    // --- Profile settings (ProviderProfile-gated) ---

    public function test_guest_cannot_update_online_status(): void
    {
        $this->postJson(route('online.update-status'), [])->assertUnauthorized();
    }

    public function test_guest_cannot_update_available_status(): void
    {
        $this->postJson(route('available.update-status'), [])->assertUnauthorized();
    }

    public function test_guest_cannot_update_availability(): void
    {
        $this->postJson(route('availability.update'), [])->assertUnauthorized();
    }

    public function test_guest_cannot_store_profile_message(): void
    {
        $this->postJson(route('profile-message.store'), [])->assertUnauthorized();
    }

    public function test_guest_cannot_update_hide_show_profile(): void
    {
        $this->postJson(route('update-hide-show-profile'), [])->assertUnauthorized();
    }

    // ===============================================================
    // 2. Cross-user — cannot modify another user's owned resources
    // ===============================================================

    // --- Photos ---

    public function test_user_cannot_set_cover_on_another_users_photo(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();
        $photo = $this->makePhoto($owner);

        $this->actingAs($attacker)
            ->postJson(route('photos.setCover', $photo))
            ->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_photo(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();
        $photo = $this->makePhoto($owner);

        $this->actingAs($attacker)
            ->deleteJson(route('photos.destroy', $photo))
            ->assertForbidden();
    }

    // --- Videos ---

    public function test_user_cannot_delete_another_users_video(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();
        $video = $this->makeVideo($owner);

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($attacker)
            ->deleteJson(route('videos.destroy', $video))
            ->assertForbidden();
    }

    // --- Rates ---

    public function test_user_cannot_update_another_users_rate(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();
        $rate = $this->makeRate($owner);

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($attacker)
            ->putJson(route('my-rate.update', $rate), [
                'description' => 'Hacked',
                'incall' => '999',
                'outcall' => '999',
            ])
            ->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_rate(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();
        $rate = $this->makeRate($owner);

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($attacker)
            ->deleteJson(route('my-rate.destroy', $rate))
            ->assertForbidden();
    }

    // --- Rate Groups ---

    public function test_user_cannot_update_another_users_rate_group(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();
        $group = $this->makeRateGroup($owner);

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($attacker)
            ->putJson(route('my-rate.groups.update', $group), [
                'name' => 'Hacked',
            ])
            ->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_rate_group(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();
        $group = $this->makeRateGroup($owner);

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($attacker)
            ->deleteJson(route('my-rate.groups.destroy', $group))
            ->assertForbidden();
    }

    // --- Tours ---

    public function test_user_cannot_update_another_users_tour(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();
        $tour = $this->makeTour($owner);

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($attacker)
            ->putJson(route('my-tours.update', $tour), [
                'city' => 'Hacked',
                'from' => now()->addDays(1)->toDateString(),
                'to' => now()->addDays(5)->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_user_cannot_delete_another_users_tour(): void
    {
        $owner = $this->providerWithProfile();
        $attacker = $this->providerWithProfile();
        $tour = $this->makeTour($owner);

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($attacker)
            ->deleteJson(route('my-tours.destroy', $tour))
            ->assertForbidden();
    }

    // ===============================================================
    // 3. No-profile user blocked from create-gated endpoints
    // ===============================================================

    public function test_user_without_profile_cannot_create_rate(): void
    {
        $user = $this->userWithoutProfile();

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($user)
            ->postJson(route('my-rate.store'), [
                'description' => '30 min',
                'incall' => '200',
                'outcall' => '250',
            ])
            ->assertForbidden();
    }

    public function test_user_without_profile_cannot_create_tour(): void
    {
        $user = $this->userWithoutProfile();

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($user)
            ->postJson(route('my-tours.store'), [
                'city' => 'Sydney',
                'from' => now()->addDays(1)->toDateString(),
                'to' => now()->addDays(5)->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_user_without_profile_cannot_upload_verification_photo(): void
    {
        $user = $this->userWithoutProfile();

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($user)
            ->postJson(route('photo-verification.upload'), [
                'photos' => [UploadedFile::fake()->image('verify.jpg')],
            ])
            ->assertForbidden();
    }

    public function test_user_without_profile_cannot_update_short_url(): void
    {
        $user = $this->userWithoutProfile();

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($user)
            ->postJson(route('short-url.update'), ['slug' => 'test'])
            ->assertForbidden();
    }

    public function test_user_without_profile_cannot_view_my_profile(): void
    {
        $user = $this->userWithoutProfile();

        $this->actingAs($user)
            ->getJson(route('my-profile'))
            ->assertForbidden();
    }

    public function test_user_without_profile_cannot_view_edit_profile(): void
    {
        $user = $this->userWithoutProfile();

        $this->actingAs($user)
            ->get(route('edit-profile'))
            ->assertForbidden();
    }

    // ===============================================================
    // 4. Policy unit tests — every policy, owner CAN + non-owner CANNOT
    // ===============================================================

    // --- ProfileImagePolicy ---

    public function test_profile_image_policy_owner_can_view(): void
    {
        $owner = $this->providerWithProfile();
        $photo = $this->makePhoto($owner);

        $this->assertTrue(app(ProfileImagePolicy::class)->view($owner, $photo));
    }

    public function test_profile_image_policy_owner_can_update(): void
    {
        $owner = $this->providerWithProfile();
        $photo = $this->makePhoto($owner);

        $this->assertTrue(app(ProfileImagePolicy::class)->update($owner, $photo));
    }

    public function test_profile_image_policy_owner_can_delete(): void
    {
        $owner = $this->providerWithProfile();
        $photo = $this->makePhoto($owner);

        $this->assertTrue(app(ProfileImagePolicy::class)->delete($owner, $photo));
    }

    public function test_profile_image_policy_blocks_cross_user_view(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $photo = $this->makePhoto($owner);

        $this->assertFalse(app(ProfileImagePolicy::class)->view($other, $photo));
    }

    public function test_profile_image_policy_blocks_cross_user_update(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $photo = $this->makePhoto($owner);

        $this->assertFalse(app(ProfileImagePolicy::class)->update($other, $photo));
    }

    public function test_profile_image_policy_blocks_cross_user_delete(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $photo = $this->makePhoto($owner);

        $this->assertFalse(app(ProfileImagePolicy::class)->delete($other, $photo));
    }

    // --- UserVideoPolicy ---

    public function test_user_video_policy_owner_can_view(): void
    {
        $owner = $this->providerWithProfile();
        $video = $this->makeVideo($owner);

        $this->assertTrue(app(UserVideoPolicy::class)->view($owner, $video));
    }

    public function test_user_video_policy_owner_can_update(): void
    {
        $owner = $this->providerWithProfile();
        $video = $this->makeVideo($owner);

        $this->assertTrue(app(UserVideoPolicy::class)->update($owner, $video));
    }

    public function test_user_video_policy_owner_can_delete(): void
    {
        $owner = $this->providerWithProfile();
        $video = $this->makeVideo($owner);

        $this->assertTrue(app(UserVideoPolicy::class)->delete($owner, $video));
    }

    public function test_user_video_policy_blocks_cross_user_view(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $video = $this->makeVideo($owner);

        $this->assertFalse(app(UserVideoPolicy::class)->view($other, $video));
    }

    public function test_user_video_policy_blocks_cross_user_update(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $video = $this->makeVideo($owner);

        $this->assertFalse(app(UserVideoPolicy::class)->update($other, $video));
    }

    public function test_user_video_policy_blocks_cross_user_delete(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $video = $this->makeVideo($owner);

        $this->assertFalse(app(UserVideoPolicy::class)->delete($other, $video));
    }

    // --- RatePolicy ---

    public function test_rate_policy_owner_can_view(): void
    {
        $owner = $this->providerWithProfile();
        $rate = $this->makeRate($owner);

        $this->assertTrue(app(RatePolicy::class)->view($owner, $rate));
    }

    public function test_rate_policy_owner_can_update(): void
    {
        $owner = $this->providerWithProfile();
        $rate = $this->makeRate($owner);

        $this->assertTrue(app(RatePolicy::class)->update($owner, $rate));
    }

    public function test_rate_policy_owner_can_delete(): void
    {
        $owner = $this->providerWithProfile();
        $rate = $this->makeRate($owner);

        $this->assertTrue(app(RatePolicy::class)->delete($owner, $rate));
    }

    public function test_rate_policy_provider_with_profile_can_create(): void
    {
        $user = $this->providerWithProfile();

        $this->assertTrue(app(RatePolicy::class)->create($user));
    }

    public function test_rate_policy_blocks_create_without_profile(): void
    {
        $user = $this->userWithoutProfile();

        $this->assertFalse(app(RatePolicy::class)->create($user));
    }

    public function test_rate_policy_blocks_cross_user_view(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $rate = $this->makeRate($owner);

        $this->assertFalse(app(RatePolicy::class)->view($other, $rate));
    }

    public function test_rate_policy_blocks_cross_user_update(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $rate = $this->makeRate($owner);

        $this->assertFalse(app(RatePolicy::class)->update($other, $rate));
    }

    public function test_rate_policy_blocks_cross_user_delete(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $rate = $this->makeRate($owner);

        $this->assertFalse(app(RatePolicy::class)->delete($other, $rate));
    }

    // --- RateGroupPolicy ---

    public function test_rate_group_policy_owner_can_view(): void
    {
        $owner = $this->providerWithProfile();
        $group = $this->makeRateGroup($owner);

        $this->assertTrue(app(RateGroupPolicy::class)->view($owner, $group));
    }

    public function test_rate_group_policy_owner_can_update(): void
    {
        $owner = $this->providerWithProfile();
        $group = $this->makeRateGroup($owner);

        $this->assertTrue(app(RateGroupPolicy::class)->update($owner, $group));
    }

    public function test_rate_group_policy_owner_can_delete(): void
    {
        $owner = $this->providerWithProfile();
        $group = $this->makeRateGroup($owner);

        $this->assertTrue(app(RateGroupPolicy::class)->delete($owner, $group));
    }

    public function test_rate_group_policy_blocks_cross_user_view(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $group = $this->makeRateGroup($owner);

        $this->assertFalse(app(RateGroupPolicy::class)->view($other, $group));
    }

    public function test_rate_group_policy_blocks_cross_user_update(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $group = $this->makeRateGroup($owner);

        $this->assertFalse(app(RateGroupPolicy::class)->update($other, $group));
    }

    public function test_rate_group_policy_blocks_cross_user_delete(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $group = $this->makeRateGroup($owner);

        $this->assertFalse(app(RateGroupPolicy::class)->delete($other, $group));
    }

    // --- TourPolicy ---

    public function test_tour_policy_owner_can_view(): void
    {
        $owner = $this->providerWithProfile();
        $tour = $this->makeTour($owner);

        $this->assertTrue(app(TourPolicy::class)->view($owner, $tour));
    }

    public function test_tour_policy_owner_can_update(): void
    {
        $owner = $this->providerWithProfile();
        $tour = $this->makeTour($owner);

        $this->assertTrue(app(TourPolicy::class)->update($owner, $tour));
    }

    public function test_tour_policy_owner_can_delete(): void
    {
        $owner = $this->providerWithProfile();
        $tour = $this->makeTour($owner);

        $this->assertTrue(app(TourPolicy::class)->delete($owner, $tour));
    }

    public function test_tour_policy_provider_with_profile_can_create(): void
    {
        $user = $this->providerWithProfile();

        $this->assertTrue(app(TourPolicy::class)->create($user));
    }

    public function test_tour_policy_blocks_create_without_profile(): void
    {
        $user = $this->userWithoutProfile();

        $this->assertFalse(app(TourPolicy::class)->create($user));
    }

    public function test_tour_policy_blocks_cross_user_view(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $tour = $this->makeTour($owner);

        $this->assertFalse(app(TourPolicy::class)->view($other, $tour));
    }

    public function test_tour_policy_blocks_cross_user_update(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $tour = $this->makeTour($owner);

        $this->assertFalse(app(TourPolicy::class)->update($other, $tour));
    }

    public function test_tour_policy_blocks_cross_user_delete(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $tour = $this->makeTour($owner);

        $this->assertFalse(app(TourPolicy::class)->delete($other, $tour));
    }

    // --- PhotoVerificationPolicy ---

    public function test_photo_verification_policy_owner_can_view(): void
    {
        $owner = $this->providerWithProfile();
        $pv = $this->makePhotoVerification($owner);

        $this->assertTrue(app(PhotoVerificationPolicy::class)->view($owner, $pv));
    }

    public function test_photo_verification_policy_owner_can_delete(): void
    {
        $owner = $this->providerWithProfile();
        $pv = $this->makePhotoVerification($owner);

        $this->assertTrue(app(PhotoVerificationPolicy::class)->delete($owner, $pv));
    }

    public function test_photo_verification_policy_provider_can_create(): void
    {
        $user = $this->providerWithProfile();

        $this->assertTrue(app(PhotoVerificationPolicy::class)->create($user));
    }

    public function test_photo_verification_policy_blocks_create_without_profile(): void
    {
        $user = $this->userWithoutProfile();

        $this->assertFalse(app(PhotoVerificationPolicy::class)->create($user));
    }

    public function test_photo_verification_policy_blocks_cross_user_view(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $pv = $this->makePhotoVerification($owner);

        $this->assertFalse(app(PhotoVerificationPolicy::class)->view($other, $pv));
    }

    public function test_photo_verification_policy_blocks_cross_user_delete(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $pv = $this->makePhotoVerification($owner);

        $this->assertFalse(app(PhotoVerificationPolicy::class)->delete($other, $pv));
    }

    // --- ShortUrlPolicy ---

    public function test_short_url_policy_owner_can_view(): void
    {
        $owner = $this->providerWithProfile();
        $url = $this->makeShortUrl($owner);

        $this->assertTrue(app(ShortUrlPolicy::class)->view($owner, $url));
    }

    public function test_short_url_policy_owner_can_update(): void
    {
        $owner = $this->providerWithProfile();
        $url = $this->makeShortUrl($owner);

        $this->assertTrue(app(ShortUrlPolicy::class)->update($owner, $url));
    }

    public function test_short_url_policy_provider_can_create(): void
    {
        $user = $this->providerWithProfile();

        $this->assertTrue(app(ShortUrlPolicy::class)->create($user));
    }

    public function test_short_url_policy_blocks_create_without_profile(): void
    {
        $user = $this->userWithoutProfile();

        $this->assertFalse(app(ShortUrlPolicy::class)->create($user));
    }

    public function test_short_url_policy_blocks_cross_user_view(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $url = $this->makeShortUrl($owner);

        $this->assertFalse(app(ShortUrlPolicy::class)->view($other, $url));
    }

    public function test_short_url_policy_blocks_cross_user_update(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $url = $this->makeShortUrl($owner);

        $this->assertFalse(app(ShortUrlPolicy::class)->update($other, $url));
    }

    // --- ProviderProfilePolicy ---

    public function test_provider_profile_policy_allows_view_with_profile(): void
    {
        $user = $this->providerWithProfile();

        $this->assertTrue(app(ProviderProfilePolicy::class)->view($user));
    }

    public function test_provider_profile_policy_blocks_view_without_profile(): void
    {
        $user = $this->userWithoutProfile();

        $this->assertFalse(app(ProviderProfilePolicy::class)->view($user));
    }

    public function test_provider_profile_policy_allows_update_with_profile(): void
    {
        $user = $this->providerWithProfile();

        $this->assertTrue(app(ProviderProfilePolicy::class)->update($user));
    }

    public function test_provider_profile_policy_blocks_update_without_profile(): void
    {
        $user = $this->userWithoutProfile();

        $this->assertFalse(app(ProviderProfilePolicy::class)->update($user));
    }

    public function test_provider_profile_policy_allows_create_for_provider_role(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $this->assertTrue(app(ProviderProfilePolicy::class)->create($user));
    }

    public function test_provider_profile_policy_blocks_create_for_non_provider_role(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->assertFalse(app(ProviderProfilePolicy::class)->create($user));
    }

    public function test_provider_profile_policy_owner_can_view_owned(): void
    {
        $user = $this->providerWithProfile();
        $profile = $user->providerProfile;

        $this->assertTrue(app(ProviderProfilePolicy::class)->viewOwned($user, $profile));
    }

    public function test_provider_profile_policy_blocks_cross_user_view_owned(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $profile = $owner->providerProfile;

        $this->assertFalse(app(ProviderProfilePolicy::class)->viewOwned($other, $profile));
    }

    public function test_provider_profile_policy_owner_can_update_owned(): void
    {
        $user = $this->providerWithProfile();
        $profile = $user->providerProfile;

        $this->assertTrue(app(ProviderProfilePolicy::class)->updateOwned($user, $profile));
    }

    public function test_provider_profile_policy_blocks_cross_user_update_owned(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();
        $profile = $owner->providerProfile;

        $this->assertFalse(app(ProviderProfilePolicy::class)->updateOwned($other, $profile));
    }
}
