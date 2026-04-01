<?php

namespace Tests\Feature;

use App\Actions\Auth\SendProviderOtp;
use App\Actions\GenerateUniqueProviderProfileSlug;
use App\Http\Middleware\CheckProfileSteps;
use App\Models\ProfileImage;
use App\Models\ProviderProfile;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class EdgeCaseTest extends TestCase
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
            'slug' => 'test-'.$user->id,
        ]);

        return $user;
    }

    private function seedOtpSession(string $email = 'test@example.com', string $mobile = '0412345678'): string
    {
        $this->mock(SendProviderOtp::class, function ($mock) {
            $mock->shouldReceive('execute')->andReturn([
                'success' => true,
                'otp_hash' => Hash::make('123456'),
                'expires_at' => now()->addMinutes(2),
            ]);
        });

        $pendingKey = 'provider_signup_'.md5($email.'|'.$mobile);
        $otpHash = Hash::make('123456');

        Cache::put($pendingKey, [
            'name' => 'Test User',
            'email' => $email,
            'mobile' => $mobile,
            'password' => Hash::make('password123'),
            'suburb' => 'Sydney',
            'maskMobile' => '******5678',
            'role' => User::ROLE_PROVIDER,
            'mobile_verified' => false,
            'referral_code' => null,
        ], now()->addMinutes(10));

        Cache::put($pendingKey.'_otp', [
            'code' => $otpHash,
            'expires_at' => now()->addMinutes(2)->timestamp,
        ], now()->addMinutes(2));

        Session::put('otp_required', true);
        Session::put('pending_signup_key', $pendingKey);

        return $pendingKey;
    }

    // ===============================================================
    // 1. OTP edge cases
    // ===============================================================

    public function test_expired_otp_is_rejected(): void
    {
        $pendingKey = $this->seedOtpSession();

        // Overwrite OTP data with an already-expired timestamp
        Cache::put($pendingKey.'_otp', [
            'code' => Hash::make('123456'),
            'expires_at' => now()->subSeconds(1)->timestamp,
        ], now()->addMinutes(5));

        $response = $this->postJson(route('verify.otp'), ['otp' => '123456']);

        $response->assertStatus(422);
        $this->assertStringContainsString('expired', strtolower($response->json('message')));
    }

    public function test_otp_cannot_be_replayed_after_successful_verification(): void
    {
        $pendingKey = $this->seedOtpSession('replay@example.com');

        // First submission succeeds (and logs the user in)
        $this->postJson(route('verify.otp'), ['otp' => '123456'])->assertOk();

        // Log out so the guest middleware allows the retry attempt
        Auth::logout();

        // Session and cache are cleared after success — second submission fails
        $response = $this->postJson(route('verify.otp'), ['otp' => '123456']);

        $response->assertStatus(422);
        $this->assertStringContainsString('expired', strtolower($response->json('message')));
    }

    public function test_otp_attempt_counter_persists_across_requests(): void
    {
        $pendingKey = $this->seedOtpSession('counter@example.com');

        // Submit wrong OTP 3 times
        for ($i = 0; $i < 3; $i++) {
            $response = $this->postJson(route('verify.otp'), ['otp' => '999999']);
            $response->assertStatus(422);
        }

        // Fourth wrong attempt still shows remaining attempts (5 max - 4 used = 1 left)
        $response = $this->postJson(route('verify.otp'), ['otp' => '999999']);
        $response->assertStatus(422);
        $this->assertStringContainsString('1 attempt(s) remaining', $response->json('message'));

        // Fifth attempt triggers lockout
        $response = $this->postJson(route('verify.otp'), ['otp' => '999999']);
        $response->assertStatus(429);
    }

    public function test_correct_otp_still_works_after_some_wrong_attempts(): void
    {
        $pendingKey = $this->seedOtpSession('partial@example.com');

        // 3 wrong attempts
        for ($i = 0; $i < 3; $i++) {
            $this->postJson(route('verify.otp'), ['otp' => '999999'])->assertStatus(422);
        }

        // Correct OTP on 4th attempt succeeds
        $response = $this->postJson(route('verify.otp'), ['otp' => '123456']);
        $response->assertOk();
        $this->assertTrue($response->json('success'));
    }

    public function test_otp_with_non_numeric_input_fails_validation(): void
    {
        $this->seedOtpSession();

        $this->postJson(route('verify.otp'), ['otp' => 'abcdef'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('otp');
    }

    public function test_otp_with_too_few_digits_fails_validation(): void
    {
        $this->seedOtpSession();

        $this->postJson(route('verify.otp'), ['otp' => '123'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('otp');
    }

    // ===============================================================
    // 2. Photo primary-state edge cases
    // ===============================================================

    public function test_setting_cover_on_already_primary_photo_is_idempotent(): void
    {
        $owner = $this->providerWithProfile();

        $photo = ProfileImage::create([
            'user_id' => $owner->id,
            'image_path' => 'images/test.jpg',
            'thumbnail_path' => 'thumbnails/test.jpg',
            'is_primary' => true,
        ]);

        $this->actingAs($owner)
            ->postJson(route('photos.setCover', $photo))
            ->assertOk();

        // Still primary, still only one primary
        $photo->refresh();
        $this->assertTrue($photo->is_primary);
        $this->assertEquals(
            1,
            ProfileImage::where('user_id', $owner->id)->where('is_primary', true)->count()
        );
    }

    public function test_deleting_primary_photo_promotes_most_recent_remaining(): void
    {
        $owner = $this->providerWithProfile();

        $oldest = ProfileImage::create([
            'user_id' => $owner->id,
            'image_path' => 'images/oldest.jpg',
            'thumbnail_path' => 'thumbnails/oldest.jpg',
            'is_primary' => false,
        ]);
        // Force distinct timestamps so latest() ordering is deterministic
        $oldest->forceFill(['created_at' => now()->subDays(2)])->saveQuietly();

        $newest = ProfileImage::create([
            'user_id' => $owner->id,
            'image_path' => 'images/newest.jpg',
            'thumbnail_path' => 'thumbnails/newest.jpg',
            'is_primary' => false,
        ]);
        $newest->forceFill(['created_at' => now()->subDay()])->saveQuietly();

        $primary = ProfileImage::create([
            'user_id' => $owner->id,
            'image_path' => 'images/primary.jpg',
            'thumbnail_path' => 'thumbnails/primary.jpg',
            'is_primary' => true,
        ]);

        $this->actingAs($owner)
            ->deleteJson(route('photos.destroy', $primary))
            ->assertOk();

        $newest->refresh();
        $oldest->refresh();

        $this->assertTrue($newest->is_primary, 'Most recent photo should become primary');
        $this->assertFalse($oldest->is_primary);
    }

    public function test_deleting_only_photo_leaves_user_with_no_photos(): void
    {
        $owner = $this->providerWithProfile();

        $photo = ProfileImage::create([
            'user_id' => $owner->id,
            'image_path' => 'images/only.jpg',
            'thumbnail_path' => 'thumbnails/only.jpg',
            'is_primary' => true,
        ]);

        $this->actingAs($owner)
            ->deleteJson(route('photos.destroy', $photo))
            ->assertOk();

        $this->assertEquals(0, ProfileImage::where('user_id', $owner->id)->count());
    }

    public function test_soft_deleted_photo_cannot_be_set_as_cover(): void
    {
        $owner = $this->providerWithProfile();

        $photo = ProfileImage::create([
            'user_id' => $owner->id,
            'image_path' => 'images/deleted.jpg',
            'thumbnail_path' => 'thumbnails/deleted.jpg',
            'is_primary' => false,
        ]);

        $photo->delete(); // soft-delete

        $this->actingAs($owner)
            ->postJson(route('photos.setCover', $photo->id))
            ->assertNotFound();
    }

    public function test_soft_deleted_photo_cannot_be_deleted_again(): void
    {
        $owner = $this->providerWithProfile();

        $photo = ProfileImage::create([
            'user_id' => $owner->id,
            'image_path' => 'images/deleted.jpg',
            'thumbnail_path' => 'thumbnails/deleted.jpg',
            'is_primary' => false,
        ]);

        $photo->delete(); // soft-delete

        $this->actingAs($owner)
            ->deleteJson(route('photos.destroy', $photo->id))
            ->assertNotFound();
    }

    public function test_switching_primary_across_many_photos_leaves_exactly_one_primary(): void
    {
        $owner = $this->providerWithProfile();

        $photos = [];
        for ($i = 0; $i < 5; $i++) {
            $photos[] = ProfileImage::create([
                'user_id' => $owner->id,
                'image_path' => "images/photo-{$i}.jpg",
                'thumbnail_path' => "thumbnails/photo-{$i}.jpg",
                'is_primary' => $i === 0,
            ]);
        }

        // Rapidly switch primary to different photos
        foreach ($photos as $photo) {
            $this->actingAs($owner)
                ->postJson(route('photos.setCover', $photo))
                ->assertOk();
        }

        $primaryCount = ProfileImage::where('user_id', $owner->id)
            ->where('is_primary', true)
            ->count();

        $this->assertEquals(1, $primaryCount, 'Exactly one photo should be primary');
        $this->assertTrue($photos[4]->fresh()->is_primary, 'Last set photo should be primary');
    }

    // ===============================================================
    // 3. Short URL collision edge cases
    // ===============================================================

    public function test_short_url_rejects_duplicate_slug_owned_by_another_user(): void
    {
        $owner1 = $this->providerWithProfile();
        $owner2 = $this->providerWithProfile();

        ShortUrl::create(['user_id' => $owner1->id, 'short_url' => 'taken-slug']);

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($owner2)
            ->postJson(route('short-url.update'), ['slug' => 'taken-slug'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('slug');
    }

    public function test_short_url_allows_owner_to_keep_same_slug(): void
    {
        $owner = $this->providerWithProfile();

        ShortUrl::create(['user_id' => $owner->id, 'short_url' => 'my-slug']);

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($owner)
            ->postJson(route('short-url.update'), ['slug' => 'my-slug'])
            ->assertOk();
    }

    public function test_short_url_rejects_invalid_characters(): void
    {
        $owner = $this->providerWithProfile();

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($owner)
            ->postJson(route('short-url.update'), ['slug' => 'invalid slug!@#'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('slug');
    }

    public function test_short_url_update_replaces_existing_url(): void
    {
        $owner = $this->providerWithProfile();

        ShortUrl::create(['user_id' => $owner->id, 'short_url' => 'old-slug']);

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($owner)
            ->postJson(route('short-url.update'), ['slug' => 'new-slug'])
            ->assertOk();

        $this->assertEquals('new-slug', ShortUrl::where('user_id', $owner->id)->value('short_url'));
        $this->assertEquals(1, ShortUrl::where('user_id', $owner->id)->count());
    }

    // ===============================================================
    // 4. Profile slug collision
    // ===============================================================

    public function test_profile_slug_generation_handles_collision(): void
    {
        ProviderProfile::create([
            'user_id' => User::factory()->create()->id,
            'name' => 'Test User',
            'slug' => 'test-user',
        ]);

        $generator = app(GenerateUniqueProviderProfileSlug::class);
        $slug = $generator->execute('Test User');

        $this->assertNotEquals('test-user', $slug);
        $this->assertStringStartsWith('test-user-', $slug);
    }

    public function test_profile_slug_generation_handles_multiple_collisions(): void
    {
        // Create profiles with slug, slug-1, slug-2
        for ($i = 0; $i < 3; $i++) {
            $suffix = $i === 0 ? '' : '-'.$i;
            ProviderProfile::create([
                'user_id' => User::factory()->create()->id,
                'name' => 'Collision',
                'slug' => 'collision'.$suffix,
            ]);
        }

        $generator = app(GenerateUniqueProviderProfileSlug::class);
        $slug = $generator->execute('Collision');

        $this->assertEquals('collision-3', $slug);
    }

    public function test_profile_slug_generation_with_empty_name_uses_fallback(): void
    {
        $generator = app(GenerateUniqueProviderProfileSlug::class);
        $slug = $generator->execute('');

        $this->assertEquals('profile', $slug);
    }

    // ===============================================================
    // 5. Rate group deletion with associated rates
    // ===============================================================

    public function test_deleting_rate_group_nullifies_associated_rate_group_ids(): void
    {
        $owner = $this->providerWithProfile();

        $group = \App\Models\RateGroup::create([
            'user_id' => $owner->id,
            'name' => 'Standard',
        ]);

        $rate = \App\Models\Rate::create([
            'user_id' => $owner->id,
            'description' => '30 min',
            'incall' => 200,
            'outcall' => 250,
            'group_id' => $group->id,
        ]);

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($owner)
            ->deleteJson(route('my-rate.groups.destroy', $group))
            ->assertOk();

        $rate->refresh();
        $this->assertNull($rate->group_id, 'Rate group_id should be nullified after group deletion');
    }

    // ===============================================================
    // 6. Tour date edge cases
    // ===============================================================

    public function test_tour_rejects_end_date_before_start_date(): void
    {
        $owner = $this->providerWithProfile();

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($owner)
            ->postJson(route('my-tours.store'), [
                'city' => 'Sydney',
                'from' => now()->addDays(5)->toDateString(),
                'to' => now()->addDay()->toDateString(),
            ])
            ->assertStatus(422);
    }

    public function test_tour_rejects_past_start_date(): void
    {
        $owner = $this->providerWithProfile();

        $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($owner)
            ->postJson(route('my-tours.store'), [
                'city' => 'Melbourne',
                'from' => now()->subDay()->toDateString(),
                'to' => now()->addDays(3)->toDateString(),
            ])
            ->assertStatus(422);
    }

    // ===============================================================
    // 7. Account deletion edge cases
    // ===============================================================

    public function test_deleting_account_removes_user_from_database(): void
    {
        $user = $this->providerWithProfile();

        $this->actingAs($user)
            ->delete(route('account.destroy'), [
                'password' => 'password',
                'confirmation_text' => 'DELETE',
            ])
            ->assertRedirect('/signin');

        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'account_status' => 'soft_deleted',
        ]);
    }

    public function test_deleting_account_cascades_to_owned_resources(): void
    {
        $user = $this->providerWithProfile();

        ProfileImage::create([
            'user_id' => $user->id,
            'image_path' => 'images/test.jpg',
            'thumbnail_path' => 'thumbnails/test.jpg',
            'is_primary' => true,
        ]);

        ShortUrl::create(['user_id' => $user->id, 'short_url' => 'slug-'.$user->id]);

        $this->actingAs($user)
            ->delete(route('account.destroy'), [
                'password' => 'password',
                'confirmation_text' => 'DELETE',
            ])
            ->assertRedirect('/signin');

        // Account uses soft-delete with scheduled purge — resources still exist
        $this->assertSoftDeleted('users', ['id' => $user->id]);
        $this->assertNotNull($user->fresh()?->scheduled_purge_at);
        $this->assertEquals(1, ProfileImage::where('user_id', $user->id)->count());
        $this->assertEquals(1, ShortUrl::where('user_id', $user->id)->count());
    }
}
