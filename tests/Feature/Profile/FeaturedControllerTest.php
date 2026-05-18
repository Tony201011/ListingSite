<?php

namespace Tests\Feature\Profile;

use App\Actions\GetFeaturedState;
use App\Actions\PurchaseFeatured;
use App\Actions\Support\ActionResult;
use App\Http\Middleware\CheckProfileSteps;
use App\Http\Middleware\EnsureProfileSelected;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class FeaturedControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([CheckProfileSteps::class, EnsureProfileSelected::class]);
    }

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    private function createProvider(int $credits = 10): User
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'credits' => $credits,
        ]);

        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
        ]);

        return $user;
    }

    public function test_featured_page_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider(credits: 10);

        $getFeaturedState = Mockery::mock(GetFeaturedState::class);
        $getFeaturedState->shouldReceive('execute')
            ->once()
            ->andReturn([
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

        $response = $this->actingAs($user)->get(route('featured'));

        $response->assertOk();
        $response->assertViewIs('profile.featured');
        $response->assertViewHas('isFeatured', false);
        $response->assertViewHas('creditCost', 5);
        $response->assertViewHas('userCredits', 10);
        $response->assertSeeInOrder([
            'Boost Your Profile',
            'Ad Placement Graph',
            'Available placements',
            'How it works',
        ]);
    }

    public function test_purchase_featured_returns_success_json(): void
    {
        $user = $this->createProvider(credits: 10);
        $profile = ProviderProfile::where('user_id', $user->id)->first();

        $purchaseFeatured = Mockery::mock(PurchaseFeatured::class);
        $purchaseFeatured->shouldReceive('execute')
            ->once()
            ->andReturn(ActionResult::success([
                'is_featured' => true,
                'expires_at' => '2026-05-21T00:00:00+00:00',
                'credit_cost' => 5,
                'duration_days' => 1,
            ], 'Featured activated! Your listing is now featured for 1 day.'));

        $this->app->instance(PurchaseFeatured::class, $purchaseFeatured);

        $response = $this->actingAs($user)->postJson(route('featured.purchase'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'is_featured' => true,
            'message' => 'Featured activated! Your listing is now featured for 1 day.',
        ]);
    }

    public function test_purchase_featured_returns_error_when_insufficient_credits(): void
    {
        $user = $this->createProvider(credits: 2);
        $profile = ProviderProfile::where('user_id', $user->id)->first();

        $purchaseFeatured = Mockery::mock(PurchaseFeatured::class);
        $purchaseFeatured->shouldReceive('execute')
            ->once()
            ->andReturn(new ActionResult(
                false,
                422,
                'You need 5 credits to activate Featured. You currently have 2 credits.',
                [
                    'is_featured' => false,
                    'expires_at' => null,
                    'credit_cost' => 5,
                    'duration_days' => 1,
                ],
                'domain'
            ));

        $this->app->instance(PurchaseFeatured::class, $purchaseFeatured);

        $response = $this->actingAs($user)->postJson(route('featured.purchase'));

        $response->assertUnprocessable();
        $response->assertJson([
            'success' => false,
        ]);
    }

    public function test_featured_page_requires_authentication(): void
    {
        $response = $this->get(route('featured'));

        $response->assertRedirect();
    }
}
