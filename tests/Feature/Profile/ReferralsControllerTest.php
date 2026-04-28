<?php

namespace Tests\Feature\Profile;

use App\Actions\GetReferralPageData;
use App\Http\Middleware\CheckProfileSteps;
use App\Http\Middleware\EnsureProfileSelected;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ReferralsControllerTest extends TestCase
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

    public function test_referral_view_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider();

        $getReferralPageData = Mockery::mock(GetReferralPageData::class);
        $getReferralPageData->shouldReceive('execute')
            ->once()
            ->andReturn([
                'referralLink' => 'REF123',
                'referralCount' => 5,
            ]);

        $this->app->instance(GetReferralPageData::class, $getReferralPageData);

        $response = $this->actingAs($user)->get(route('referral'));

        $response->assertOk();
        $response->assertViewIs('profile.referral');
        $response->assertViewHas('referralLink', 'REF123');
        $response->assertViewHas('referralCount', 5);
    }

    public function test_referral_view_shows_zero_count_when_no_referrals(): void
    {
        $user = $this->createProvider();

        $getReferralPageData = Mockery::mock(GetReferralPageData::class);
        $getReferralPageData->shouldReceive('execute')
            ->once()
            ->andReturn([
                'referralLink' => null,
                'referralCount' => 0,
            ]);

        $this->app->instance(GetReferralPageData::class, $getReferralPageData);

        $response = $this->actingAs($user)->get(route('referral'));

        $response->assertOk();
        $response->assertViewHas('referralCount', 0);
    }

    public function test_guest_cannot_access_referral_page(): void
    {
        $response = $this->get(route('referral'));

        $response->assertRedirect();
    }
}
