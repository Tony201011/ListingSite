<?php

namespace Tests\Feature;

use App\Actions\GenerateUniqueProviderProfileSlug;
use App\Actions\GetShortUrlPageData;
use App\Actions\SetPrimaryProfilePhoto;
use App\Http\Middleware\CheckProfileSteps;
use App\Models\ProfileImage;
use App\Models\ProviderProfile;
use App\Models\ShortUrl;
use App\Models\TwilioSetting;
use App\Models\User;
use App\Models\UserVideo;
use App\Services\UserPhotoStorageService;
use App\Services\UserVideoStorageService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Tests\TestCase;

class EdgeCaseTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

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

    private function setupDummyTwilio(): void
    {
        TwilioSetting::create([
            'account_sid' => 'test_sid',
            'api_sid' => 'test_api_sid',
            'api_secret' => 'test_api_secret',
            'phone_number' => '+1234567890',
            'dummy_mode_enabled' => true,
            'dummy_mobile_number' => '0400000000',
            'dummy_otp' => '123456',
        ]);
    }

    private function validSignupPayload(array $overrides = []): array
    {
        return array_merge([
            'email' => 'newprovider@example.com',
            'nickname' => 'providerNick',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
            'mobile' => '0400000000',
            'suburb' => 'Sydney',
            'age_confirm' => '1',
        ], $overrides);
    }

    private function signupAndGetPendingKey(): string
    {
        $this->from('/signup')->post('/signup', $this->validSignupPayload());

        return session('pending_signup_key');
    }

    private function mockPhotoStorage(): void
    {
        $mock = Mockery::mock(UserPhotoStorageService::class);
        $mock->shouldReceive('store')->andReturnUsing(function ($user, $photo, $username) {
            $uuid = fake()->uuid();

            return [
                'image_path' => "images/{$username}/{$uuid}.jpg",
                'thumbnail_path' => "thumbnails/{$username}/{$uuid}.jpg",
                'image_url' => "https://cdn.example.com/images/{$uuid}.jpg",
                'thumbnail_url' => "https://cdn.example.com/thumbnails/{$uuid}.jpg",
            ];
        });
        $mock->shouldReceive('deletePaths')->andReturnNull();
        $this->app->instance(UserPhotoStorageService::class, $mock);
    }

    // ===============================================================
    // 1. OTP edge cases
    // ===============================================================

    public function test_expired_otp_is_rejected(): void
    {
        $this->setupDummyTwilio();
        $pendingKey = $this->signupAndGetPendingKey();

        // Manually expire the OTP by setting expires_at in the past
        $otpData = Cache::get($pendingKey . '_otp');
        Cache::put($pendingKey . '_otp', [
            'code' => $otpData['code'],
            'expires_at' => time() - 1,
        ], now()->addMinutes(10));

        $response = $this->postJson('/verify-otp', ['otp' => '123456']);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'OTP expired. Please signup again.',
        ]);
    }

    public function test_correct_otp_after_prior_failed_attempts_still_succeeds(): void
    {
        $this->setupDummyTwilio();
        $this->signupAndGetPendingKey();

        // Fail twice
        $this->postJson('/verify-otp', ['otp' => '111111']);
        $this->postJson('/verify-otp', ['otp' => '222222']);

        // Now use the correct OTP
        $response = $this->postJson('/verify-otp', ['otp' => '123456']);

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $this->assertDatabaseHas('users', ['email' => 'newprovider@example.com']);
    }

    public function test_replaying_correct_otp_after_successful_verification_fails(): void
    {
        $this->setupDummyTwilio();
        $this->signupAndGetPendingKey();

        // First verification succeeds
        $response = $this->postJson('/verify-otp', ['otp' => '123456']);
        $response->assertOk();

        // Replay the same OTP — session is cleared so this should fail
        $response = $this->postJson('/verify-otp', ['otp' => '123456']);
        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'OTP session expired. Please signup again.',
        ]);
    }

    public function test_otp_lockout_clears_session_and_cache(): void
    {
        $this->setupDummyTwilio();
        $pendingKey = $this->signupAndGetPendingKey();

        // Burn through all 5 attempts
        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/verify-otp', ['otp' => sprintf('%06d', $i)]);
        }
        $this->postJson('/verify-otp', ['otp' => '000005']);

        // Pending cache data should be purged
        $this->assertNull(Cache::get($pendingKey));
        $this->assertNull(Cache::get($pendingKey . '_otp'));
        $this->assertFalse(session()->has('otp_required'));
        $this->assertFalse(session()->has('pending_signup_key'));
    }

    public function test_correct_otp_after_lockout_still_fails(): void
    {
        $this->setupDummyTwilio();
        $this->signupAndGetPendingKey();

        // Burn through all 5 attempts to trigger lockout
        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/verify-otp', ['otp' => sprintf('%06d', $i)]);
        }
        $response = $this->postJson('/verify-otp', ['otp' => '000005']);
        $response->assertStatus(429);

        // Now try the correct OTP — should still fail because session is gone
        $response = $this->postJson('/verify-otp', ['otp' => '123456']);
        $response->assertStatus(422);
        $response->assertJson(['message' => 'OTP session expired. Please signup again.']);
    }

    public function test_otp_verify_blocks_duplicate_email_race_condition(): void
    {
        $this->setupDummyTwilio();
        $this->signupAndGetPendingKey();

        // Simulate a race: the email is taken between signup and OTP verify
        User::factory()->create(['email' => 'newprovider@example.com']);

        $response = $this->postJson('/verify-otp', ['otp' => '123456']);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'Email already exists.',
        ]);
    }

    public function test_resend_otp_during_cooldown_returns_429(): void
    {
        $this->setupDummyTwilio();
        $this->signupAndGetPendingKey();

        $this->postJson('/resend-otp')->assertOk();

        // Immediate retry within 30s cooldown
        $response = $this->postJson('/resend-otp');
        $response->assertStatus(429);
        $response->assertJson(['success' => false]);
    }

    public function test_resend_replaces_old_otp_and_new_otp_works(): void
    {
        $this->setupDummyTwilio();
        $pendingKey = $this->signupAndGetPendingKey();

        // Clear cooldown lock so we can resend
        Cache::forget($pendingKey . '_resend_lock');

        // Resend OTP (in dummy mode it's the same code, but cache entry is replaced)
        $this->postJson('/resend-otp')->assertOk();

        // Verify with the (dummy) OTP
        $response = $this->postJson('/verify-otp', ['otp' => '123456']);
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    // ===============================================================
    // 2. Photo edge cases
    // ===============================================================

    public function test_setting_already_primary_photo_as_cover_is_idempotent(): void
    {
        $user = $this->providerWithProfile();

        $primary = ProfileImage::create([
            'user_id' => $user->id,
            'image_path' => 'images/primary.jpg',
            'thumbnail_path' => 'thumbnails/primary.jpg',
            'is_primary' => true,
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('photos.setCover', $primary));

        $response->assertOk();
        $this->assertTrue($primary->fresh()->is_primary);
        $this->assertSame(
            1,
            ProfileImage::where('user_id', $user->id)->where('is_primary', true)->count()
        );
    }

    public function test_deleting_primary_promotes_most_recent_remaining(): void
    {
        Storage::fake('s3');
        $user = $this->providerWithProfile();

        $oldest = ProfileImage::create([
            'user_id' => $user->id,
            'image_path' => 'images/oldest.jpg',
            'thumbnail_path' => 'thumbnails/oldest.jpg',
            'is_primary' => false,
            'created_at' => now()->subDays(2),
            'updated_at' => now()->subDays(2),
        ]);

        $primary = ProfileImage::create([
            'user_id' => $user->id,
            'image_path' => 'images/primary.jpg',
            'thumbnail_path' => 'thumbnails/primary.jpg',
            'is_primary' => true,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        $newest = ProfileImage::create([
            'user_id' => $user->id,
            'image_path' => 'images/newest.jpg',
            'thumbnail_path' => 'thumbnails/newest.jpg',
            'is_primary' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->actingAs($user)->deleteJson(route('photos.destroy', $primary));

        // The newest remaining photo should become primary (latest())
        $this->assertTrue($newest->fresh()->is_primary);
        $this->assertFalse($oldest->fresh()->is_primary);
    }

    public function test_batch_upload_failure_rolls_back_all_photos(): void
    {
        $user = $this->providerWithProfile();

        $callCount = 0;
        $mock = Mockery::mock(UserPhotoStorageService::class);
        $mock->shouldReceive('store')->andReturnUsing(function () use (&$callCount) {
            $callCount++;
            if ($callCount === 3) {
                throw new \RuntimeException('Storage failure on third photo');
            }

            return [
                'image_path' => "images/photo{$callCount}.jpg",
                'thumbnail_path' => "thumbnails/photo{$callCount}.jpg",
                'image_url' => "https://cdn.example.com/images/photo{$callCount}.jpg",
                'thumbnail_url' => "https://cdn.example.com/thumbnails/photo{$callCount}.jpg",
            ];
        });
        $mock->shouldReceive('deletePaths')->andReturnNull();
        $this->app->instance(UserPhotoStorageService::class, $mock);

        $response = $this->actingAs($user)->postJson('/upload-photos', [
            'photos' => [
                UploadedFile::fake()->image('photo1.jpg'),
                UploadedFile::fake()->image('photo2.jpg'),
                UploadedFile::fake()->image('photo3.jpg'),
            ],
        ]);

        $response->assertStatus(500);
        // No partial records should remain
        $this->assertSame(0, ProfileImage::where('user_id', $user->id)->count());
    }

    public function test_batch_upload_with_existing_primary_does_not_create_duplicate_primary(): void
    {
        $this->mockPhotoStorage();
        $user = $this->providerWithProfile();

        // Existing primary photo
        ProfileImage::create([
            'user_id' => $user->id,
            'image_path' => 'images/existing-primary.jpg',
            'thumbnail_path' => 'thumbnails/existing-primary.jpg',
            'is_primary' => true,
        ]);

        // Upload 3 more
        $this->actingAs($user)->postJson('/upload-photos', [
            'photos' => [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.jpg'),
                UploadedFile::fake()->image('c.jpg'),
            ],
        ])->assertOk();

        $primaryCount = ProfileImage::where('user_id', $user->id)
            ->where('is_primary', true)
            ->count();

        $this->assertSame(1, $primaryCount);
        $this->assertSame(4, ProfileImage::where('user_id', $user->id)->count());
    }

    public function test_set_cover_only_affects_owners_photos(): void
    {
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();

        $ownerPrimary = ProfileImage::create([
            'user_id' => $owner->id,
            'image_path' => 'images/owner-primary.jpg',
            'thumbnail_path' => 'thumbnails/owner-primary.jpg',
            'is_primary' => true,
        ]);

        $ownerSecond = ProfileImage::create([
            'user_id' => $owner->id,
            'image_path' => 'images/owner-second.jpg',
            'thumbnail_path' => 'thumbnails/owner-second.jpg',
            'is_primary' => false,
        ]);

        $otherPrimary = ProfileImage::create([
            'user_id' => $other->id,
            'image_path' => 'images/other-primary.jpg',
            'thumbnail_path' => 'thumbnails/other-primary.jpg',
            'is_primary' => true,
        ]);

        // Owner sets second photo as cover
        $this->actingAs($owner)
            ->postJson(route('photos.setCover', $ownerSecond))
            ->assertOk();

        // Other user's primary is untouched
        $this->assertTrue($otherPrimary->fresh()->is_primary);
        // Owner's second photo is now primary
        $this->assertTrue($ownerSecond->fresh()->is_primary);
        $this->assertFalse($ownerPrimary->fresh()->is_primary);
    }

    // ===============================================================
    // 3. Video edge cases
    // ===============================================================

    public function test_video_batch_upload_partial_storage_failure_rolls_back(): void
    {
        $this->withoutMiddleware(CheckProfileSteps::class);
        $user = $this->providerWithProfile();

        $callCount = 0;
        $mock = Mockery::mock(UserVideoStorageService::class);
        $mock->shouldReceive('store')->andReturnUsing(function () use (&$callCount) {
            $callCount++;
            if ($callCount === 2) {
                throw new \RuntimeException('Storage failure on second video');
            }

            return [
                'video_path' => "videos/video{$callCount}.mp4",
                'video_url' => "https://cdn.example.com/videos/video{$callCount}.mp4",
            ];
        });
        $mock->shouldReceive('deletePath')->andReturnNull();
        $this->app->instance(UserVideoStorageService::class, $mock);

        $response = $this->actingAs($user)->postJson('/videos/upload', [
            'videos' => [
                UploadedFile::fake()->create('video1.mp4', 1024, 'video/mp4'),
                UploadedFile::fake()->create('video2.mp4', 1024, 'video/mp4'),
            ],
        ]);

        $response->assertStatus(500);
        $this->assertSame(0, UserVideo::where('user_id', $user->id)->count());
    }

    // ===============================================================
    // 4. Short URL / slug collision edge cases
    // ===============================================================

    public function test_short_url_rejects_duplicate_slug_owned_by_another_user(): void
    {
        $this->withoutMiddleware(CheckProfileSteps::class);
        $owner = $this->providerWithProfile();
        $other = $this->providerWithProfile();

        ShortUrl::create([
            'user_id' => $other->id,
            'short_url' => 'taken-slug',
        ]);

        $response = $this->actingAs($owner)
            ->postJson(route('short-url.update'), ['slug' => 'taken-slug']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['slug']);
    }

    public function test_short_url_allows_user_to_keep_own_slug(): void
    {
        $this->withoutMiddleware(CheckProfileSteps::class);
        $user = $this->providerWithProfile();

        ShortUrl::create([
            'user_id' => $user->id,
            'short_url' => 'my-slug',
        ]);

        $response = $this->actingAs($user)
            ->postJson(route('short-url.update'), ['slug' => 'my-slug']);

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_short_url_update_is_idempotent(): void
    {
        $this->withoutMiddleware(CheckProfileSteps::class);
        $user = $this->providerWithProfile();

        ShortUrl::create([
            'user_id' => $user->id,
            'short_url' => 'original-slug',
        ]);

        // Update twice to a new slug
        $this->actingAs($user)
            ->postJson(route('short-url.update'), ['slug' => 'new-slug'])
            ->assertOk();

        $this->actingAs($user)
            ->postJson(route('short-url.update'), ['slug' => 'new-slug'])
            ->assertOk();

        $this->assertSame(1, ShortUrl::where('user_id', $user->id)->count());
        $this->assertSame('new-slug', ShortUrl::where('user_id', $user->id)->first()->short_url);
    }

    public function test_short_url_rejects_invalid_characters(): void
    {
        $this->withoutMiddleware(CheckProfileSteps::class);
        $user = $this->providerWithProfile();

        $response = $this->actingAs($user)
            ->postJson(route('short-url.update'), ['slug' => 'invalid slug!@#']);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['slug']);
    }

    public function test_profile_slug_generation_appends_suffix_on_collision(): void
    {
        $generator = app(GenerateUniqueProviderProfileSlug::class);

        // Create profiles that will collide
        $user1 = $this->providerWithProfile();
        $user1->providerProfile->update(['slug' => 'jane']);

        $slug = $generator->execute('Jane');
        $this->assertSame('jane-1', $slug);
    }

    public function test_profile_slug_generation_increments_through_multiple_collisions(): void
    {
        $generator = app(GenerateUniqueProviderProfileSlug::class);

        // Create several colliding slugs
        foreach (['jane', 'jane-1', 'jane-2'] as $existingSlug) {
            $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
            ProviderProfile::create([
                'user_id' => $user->id,
                'name' => 'Jane',
                'slug' => $existingSlug,
            ]);
        }

        $slug = $generator->execute('Jane');
        $this->assertSame('jane-3', $slug);
    }

    public function test_profile_slug_generation_handles_empty_name(): void
    {
        $generator = app(GenerateUniqueProviderProfileSlug::class);

        $slug = $generator->execute('');
        $this->assertSame('profile', $slug);
    }

    public function test_short_url_auto_generation_produces_unique_slug(): void
    {
        $user1 = $this->providerWithProfile();
        $user2 = $this->providerWithProfile();

        $action = app(GetShortUrlPageData::class);

        $result1 = $action->execute($user1);
        $result2 = $action->execute($user2);

        $this->assertNotEquals($result1['slug'], $result2['slug']);
    }
}
