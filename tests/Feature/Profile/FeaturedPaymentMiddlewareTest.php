<?php

namespace Tests\Feature\Profile;

use App\Actions\GetFeaturedState;
use App\Actions\PurchaseFeatured;
use App\Actions\Support\ActionResult;
use App\Models\ProfileImage;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

/**
 * Verifies that the featured-listing payment routes are accessible regardless
 * of whether the active profile has photos uploaded.  These tests run with the
 * real middleware stack (no global withoutMiddleware bypass).
 */
class FeaturedPaymentMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    private function createProvider(): User
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'credits' => 10,
        ]);

        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name'    => $user->name,
            'slug'    => 'provider-'.$user->id,
        ]);

        return $user;
    }

    private function actingAsProvider(User $user): static
    {
        $profile = $user->providerProfiles()->first();

        return $this->actingAs($user)->withSession([
            'active_provider_profile_id' => $profile?->id,
        ]);
    }

    // ---------------------------------------------------------------
    // GET /featured-listing — page accessible without photos
    // ---------------------------------------------------------------

    public function test_featured_page_is_not_blocked_when_profile_has_no_photos(): void
    {
        $user = $this->createProvider();

        $getFeaturedState = Mockery::mock(GetFeaturedState::class);
        $getFeaturedState->shouldReceive('execute')->andReturn([
            'isFeatured' => false,
            'expiresAt' => null,
            'creditCost' => 5,
            'durationDays' => 1,
            'homeFeaturedExpiresAt' => null,
            'localBannerExpiresAt' => null,
            'homeBannerExpiresAt' => null,
            'freeListingExpiresAt' => null,
            'settings' => [
                'free_listing_days' => 21,
                'featured_duration_days' => 1,
                'normal_featured_credit_cost' => 1,
                'home_featured_credit_cost' => 3,
                'local_banner_credit_cost' => 2,
                'home_banner_credit_cost' => 5,
            ],
        ]);

        $this->app->instance(GetFeaturedState::class, $getFeaturedState);

        $response = $this->actingAsProvider($user)->get(route('featured'));

        // If CheckProfileSteps were on this route, it would redirect to my-profile
        // when photos are missing.  After the fix the route is outside that group,
        // so no such redirect must occur.
        $this->assertNotEquals(
            route('my-profile'),
            $response->headers->get('Location'),
            'Route must not be blocked by a missing-photos redirect'
        );
    }

    public function test_featured_page_is_also_not_blocked_when_profile_has_photos(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfiles()->first();

        ProfileImage::query()->create([
            'user_id'             => $user->id,
            'provider_profile_id' => $profile->id,
            'image_path'          => 'images/photo.jpg',
            'thumbnail_path'      => 'thumbnails/photo.jpg',
        ]);

        $getFeaturedState = Mockery::mock(GetFeaturedState::class);
        $getFeaturedState->shouldReceive('execute')->andReturn([
            'isFeatured' => false,
            'expiresAt' => null,
            'creditCost' => 5,
            'durationDays' => 1,
            'homeFeaturedExpiresAt' => null,
            'localBannerExpiresAt' => null,
            'homeBannerExpiresAt' => null,
            'freeListingExpiresAt' => null,
            'settings' => [
                'free_listing_days' => 21,
                'featured_duration_days' => 1,
                'normal_featured_credit_cost' => 1,
                'home_featured_credit_cost' => 3,
                'local_banner_credit_cost' => 2,
                'home_banner_credit_cost' => 5,
            ],
        ]);

        $this->app->instance(GetFeaturedState::class, $getFeaturedState);

        $response = $this->actingAsProvider($user)->get(route('featured'));

        $this->assertNotEquals(
            route('my-profile'),
            $response->headers->get('Location'),
            'Route must not be blocked by a missing-photos redirect'
        );
    }

    // ---------------------------------------------------------------
    // POST /featured-listing/purchase — purchase accessible without photos
    // ---------------------------------------------------------------

    public function test_featured_purchase_is_not_blocked_when_profile_has_no_photos(): void
    {
        $user = $this->createProvider();

        $purchaseFeatured = Mockery::mock(PurchaseFeatured::class);
        $purchaseFeatured->shouldReceive('execute')->once()->andReturn(
            ActionResult::success([
                'tier' => 'normal',
                'is_featured' => true,
                'expires_at' => '2026-06-05T00:00:00+00:00',
                'credit_cost' => 1,
                'duration_days' => 1,
            ], 'Featured activated!')
        );

        $this->app->instance(PurchaseFeatured::class, $purchaseFeatured);

        $response = $this->actingAsProvider($user)->postJson(route('featured.purchase'));

        // Must reach the controller and return a JSON success — no redirect to
        // my-profile because of missing photos.
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    public function test_featured_purchase_is_also_successful_when_profile_has_photos(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfiles()->first();

        ProfileImage::query()->create([
            'user_id'             => $user->id,
            'provider_profile_id' => $profile->id,
            'image_path'          => 'photo.jpg',
        ]);

        $purchaseFeatured = Mockery::mock(PurchaseFeatured::class);
        $purchaseFeatured->shouldReceive('execute')->once()->andReturn(
            ActionResult::success([
                'tier' => 'normal',
                'is_featured' => true,
                'expires_at' => '2026-06-05T00:00:00+00:00',
                'credit_cost' => 1,
                'duration_days' => 1,
            ], 'Featured activated!')
        );

        $this->app->instance(PurchaseFeatured::class, $purchaseFeatured);

        $response = $this->actingAsProvider($user)->postJson(route('featured.purchase'));

        $response->assertOk();
        $response->assertJson(['success' => true]);
    }
}

