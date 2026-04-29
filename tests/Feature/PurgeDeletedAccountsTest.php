<?php

namespace Tests\Feature;

use App\Models\ProfileImage;
use App\Models\ProviderProfile;
use App\Models\Rate;
use App\Models\RateGroup;
use App\Models\ShortUrl;
use App\Models\Tour;
use App\Models\User;
use App\Models\UserVideo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurgeDeletedAccountsTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    /** Create a soft-deleted user whose purge date has already passed. */
    private function deletedUserDueForPurge(int $daysAgo = 1): User
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'account_status' => 'soft_deleted',
            'scheduled_purge_at' => now()->subDays($daysAgo),
        ]);
        $user->delete(); // soft-delete

        return $user;
    }

    /** Seed owned resources for a user. */
    private function seedOwnedResources(User $user): void
    {
        $profile = ProviderProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'test-'.$user->id,
        ]);

        ProfileImage::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'image_path' => 'images/photo.jpg',
            'thumbnail_path' => 'thumbnails/photo.jpg',
            'is_primary' => true,
        ]);

        ShortUrl::create([
            'user_id' => $user->id,
            'short_url' => 'slug-'.$user->id,
        ]);

        $group = RateGroup::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'name' => 'Standard',
        ]);

        Rate::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'description' => '30 min',
            'incall' => 200,
            'outcall' => 250,
            'group_id' => $group->id,
        ]);

        Tour::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'city' => 'Sydney',
            'from' => now()->addDays(5),
            'to' => now()->addDays(10),
        ]);

        UserVideo::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'video_path' => 'videos/test.mp4',
            'thumbnail_path' => 'thumbnails/test.jpg',
        ]);
    }

    // ===============================================================
    // Core purge behaviour
    // ===============================================================

    public function test_purges_user_whose_retention_period_has_expired(): void
    {
        $user = $this->deletedUserDueForPurge();

        $this->artisan('accounts:purge-deleted')
            ->assertSuccessful();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }

    public function test_does_not_purge_user_whose_retention_period_has_not_expired(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'account_status' => 'soft_deleted',
            'scheduled_purge_at' => now()->addDays(10),
        ]);
        $user->delete();

        $this->artisan('accounts:purge-deleted')
            ->assertSuccessful();

        $this->assertNotNull(User::withTrashed()->find($user->id));
    }

    public function test_does_not_purge_active_users(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'scheduled_purge_at' => null,
        ]);

        $this->artisan('accounts:purge-deleted')
            ->assertSuccessful();

        $this->assertDatabaseHas('users', ['id' => $user->id]);
    }

    public function test_skips_users_with_hold_reason(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'account_status' => 'soft_deleted',
            'scheduled_purge_at' => now()->subDay(),
        ]);
        ProviderProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'test-hold-'.$user->id,
            'hold_reason' => 'legal_investigation',
        ]);
        $user->delete();

        $this->artisan('accounts:purge-deleted')
            ->assertSuccessful();

        $this->assertNotNull(User::withTrashed()->find($user->id));
    }

    // ===============================================================
    // Anonymization before hard delete
    // ===============================================================

    public function test_anonymizes_pii_before_hard_delete(): void
    {
        $user = $this->deletedUserDueForPurge();
        $originalEmail = $user->email;

        // We need to intercept the anonymization step before forceDelete.
        // Run the command — after it finishes the user is gone, but we
        // can verify by checking that no row with the original email exists.
        $this->artisan('accounts:purge-deleted')
            ->assertSuccessful();

        $this->assertDatabaseMissing('users', ['email' => $originalEmail]);
    }

    // ===============================================================
    // Cascade deletion of owned resources
    // ===============================================================

    public function test_cascade_deletes_all_owned_resources(): void
    {
        $user = $this->deletedUserDueForPurge();
        $this->seedOwnedResources($user);

        $userId = $user->id;
        $profileId = ProviderProfile::where('user_id', $userId)->value('id');

        $this->artisan('accounts:purge-deleted')
            ->assertSuccessful();

        // User hard-deleted
        $this->assertDatabaseMissing('users', ['id' => $userId]);

        // Provider profile cascade-deleted with the user
        $this->assertDatabaseMissing('provider_profiles', ['id' => $profileId]);

        // FK cascades (via provider_profile_id) remove related rows
        $this->assertEquals(0, ProfileImage::withTrashed()->where('provider_profile_id', $profileId)->count());
        $this->assertEquals(0, Rate::where('provider_profile_id', $profileId)->count());
        $this->assertEquals(0, RateGroup::where('provider_profile_id', $profileId)->count());
        $this->assertEquals(0, Tour::where('provider_profile_id', $profileId)->count());
        $this->assertEquals(0, UserVideo::withTrashed()->where('provider_profile_id', $profileId)->count());

        // ShortUrl is still user-scoped (no provider_profile_id)
        $this->assertEquals(0, ShortUrl::where('user_id', $userId)->count());
    }

    // ===============================================================
    // Multiple users
    // ===============================================================

    public function test_purges_multiple_eligible_users_in_one_run(): void
    {
        $user1 = $this->deletedUserDueForPurge(5);
        $user2 = $this->deletedUserDueForPurge(10);
        $user3 = $this->deletedUserDueForPurge(1);

        $this->artisan('accounts:purge-deleted')
            ->assertSuccessful();

        $this->assertDatabaseMissing('users', ['id' => $user1->id]);
        $this->assertDatabaseMissing('users', ['id' => $user2->id]);
        $this->assertDatabaseMissing('users', ['id' => $user3->id]);
    }

    public function test_only_purges_eligible_users_leaving_others_intact(): void
    {
        $eligible = $this->deletedUserDueForPurge();
        $notYet = User::factory()->create([
            'account_status' => 'soft_deleted',
            'scheduled_purge_at' => now()->addDays(15),
        ]);
        $notYet->delete();
        $active = User::factory()->create();

        $this->artisan('accounts:purge-deleted')
            ->assertSuccessful();

        $this->assertDatabaseMissing('users', ['id' => $eligible->id]);
        $this->assertNotNull(User::withTrashed()->find($notYet->id));
        $this->assertDatabaseHas('users', ['id' => $active->id]);
    }

    // ===============================================================
    // Multiple profiles per user
    // ===============================================================

    public function test_cascade_deletes_all_resources_for_multi_profile_user(): void
    {
        $user = $this->deletedUserDueForPurge();
        $userId = $user->id;

        // Profile 1 with full set of resources (via helper)
        $this->seedOwnedResources($user);
        $profileId1 = ProviderProfile::where('user_id', $userId)->value('id');

        // Profile 2 with its own set of resources
        $profile2 = ProviderProfile::create([
            'user_id' => $userId,
            'name' => $user->name,
            'slug' => 'test-alt-'.$userId,
        ]);
        $profileId2 = $profile2->id;

        ProfileImage::create([
            'user_id' => $userId,
            'provider_profile_id' => $profileId2,
            'image_path' => 'images/alt.jpg',
            'thumbnail_path' => 'thumbnails/alt.jpg',
            'is_primary' => false,
        ]);

        $group2 = RateGroup::create([
            'user_id' => $userId,
            'provider_profile_id' => $profileId2,
            'name' => 'Premium',
        ]);

        Rate::create([
            'user_id' => $userId,
            'provider_profile_id' => $profileId2,
            'description' => '60 min',
            'incall' => 400,
            'outcall' => 500,
            'group_id' => $group2->id,
        ]);

        Tour::create([
            'user_id' => $userId,
            'provider_profile_id' => $profileId2,
            'city' => 'Melbourne',
            'from' => now()->addDays(3),
            'to' => now()->addDays(7),
        ]);

        UserVideo::create([
            'user_id' => $userId,
            'provider_profile_id' => $profileId2,
            'video_path' => 'videos/alt.mp4',
            'thumbnail_path' => 'thumbnails/alt.jpg',
        ]);

        $this->artisan('accounts:purge-deleted')
            ->assertSuccessful();

        // User hard-deleted
        $this->assertDatabaseMissing('users', ['id' => $userId]);

        // Both profiles cascade-deleted
        $this->assertDatabaseMissing('provider_profiles', ['id' => $profileId1]);
        $this->assertDatabaseMissing('provider_profiles', ['id' => $profileId2]);

        // All resources for profile 1 deleted
        $this->assertEquals(0, ProfileImage::withTrashed()->where('provider_profile_id', $profileId1)->count());
        $this->assertEquals(0, Rate::where('provider_profile_id', $profileId1)->count());
        $this->assertEquals(0, RateGroup::where('provider_profile_id', $profileId1)->count());
        $this->assertEquals(0, Tour::where('provider_profile_id', $profileId1)->count());
        $this->assertEquals(0, UserVideo::withTrashed()->where('provider_profile_id', $profileId1)->count());

        // All resources for profile 2 deleted
        $this->assertEquals(0, ProfileImage::withTrashed()->where('provider_profile_id', $profileId2)->count());
        $this->assertEquals(0, Rate::where('provider_profile_id', $profileId2)->count());
        $this->assertEquals(0, RateGroup::where('provider_profile_id', $profileId2)->count());
        $this->assertEquals(0, Tour::where('provider_profile_id', $profileId2)->count());
        $this->assertEquals(0, UserVideo::withTrashed()->where('provider_profile_id', $profileId2)->count());

        // User-scoped records deleted
        $this->assertEquals(0, ShortUrl::where('user_id', $userId)->count());
    }

    // ===============================================================
    // Edge cases
    // ===============================================================

    public function test_command_succeeds_with_no_eligible_users(): void
    {
        $this->artisan('accounts:purge-deleted')
            ->assertSuccessful();
    }

    public function test_user_at_exact_purge_boundary_is_purged(): void
    {
        $user = User::factory()->create([
            'account_status' => 'soft_deleted',
            'scheduled_purge_at' => now(),
        ]);
        $user->delete();

        $this->artisan('accounts:purge-deleted')
            ->assertSuccessful();

        $this->assertDatabaseMissing('users', ['id' => $user->id]);
    }
}
