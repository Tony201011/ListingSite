<?php

namespace Tests\Feature\Profile;

use App\Actions\GetActiveProviderProfile;
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

    private function createProvider(): array
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
        ]);

        return [$user, $profile];
    }

    public function test_referral_view_is_returned_for_authenticated_provider(): void
    {
        [$user, $profile] = $this->createProvider();

        $getActiveProviderProfile = Mockery::mock(GetActiveProviderProfile::class);
        $getActiveProviderProfile->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg instanceof User && $arg->is($user)))
            ->andReturn($profile);

        $getReferralPageData = Mockery::mock(GetReferralPageData::class);
        $getReferralPageData->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg instanceof ProviderProfile && $arg->is($profile)))
            ->andReturn([
                'referralLink' => 'REF123',
                'referralCount' => 5,
            ]);

        $this->app->instance(GetActiveProviderProfile::class, $getActiveProviderProfile);
        $this->app->instance(GetReferralPageData::class, $getReferralPageData);

        $response = $this->actingAs($user)->get(route('referral'));

        $response->assertOk();
        $response->assertViewIs('profile.referral');
        $response->assertViewHas('referralLink', 'REF123');
        $response->assertViewHas('referralCount', 5);
    }

    public function test_referral_view_shows_zero_count_when_no_referrals(): void
    {
        [$user, $profile] = $this->createProvider();

        $getActiveProviderProfile = Mockery::mock(GetActiveProviderProfile::class);
        $getActiveProviderProfile->shouldReceive('execute')
            ->once()
            ->andReturn($profile);

        $getReferralPageData = Mockery::mock(GetReferralPageData::class);
        $getReferralPageData->shouldReceive('execute')
            ->once()
            ->andReturn([
                'referralLink' => null,
                'referralCount' => 0,
            ]);

        $this->app->instance(GetActiveProviderProfile::class, $getActiveProviderProfile);
        $this->app->instance(GetReferralPageData::class, $getReferralPageData);

        $response = $this->actingAs($user)->get(route('referral'));

        $response->assertOk();
        $response->assertViewHas('referralCount', 0);
    }

    public function test_referral_uses_active_profile_not_first_profile(): void
    {
        [$user, $firstProfile] = $this->createProvider();

        $secondProfile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name.' (2)',
            'slug' => 'provider-'.$user->id.'-2',
        ]);

        $getActiveProviderProfile = Mockery::mock(GetActiveProviderProfile::class);
        $getActiveProviderProfile->shouldReceive('execute')
            ->once()
            ->andReturn($secondProfile);

        $getReferralPageData = Mockery::mock(GetReferralPageData::class);
        $getReferralPageData->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg instanceof ProviderProfile && $arg->is($secondProfile)))
            ->andReturn([
                'referralLink' => 'REF_SECOND',
                'referralCount' => 2,
            ]);

        $this->app->instance(GetActiveProviderProfile::class, $getActiveProviderProfile);
        $this->app->instance(GetReferralPageData::class, $getReferralPageData);

        $response = $this->actingAs($user)->get(route('referral'));

        $response->assertOk();
        $response->assertViewHas('referralLink', 'REF_SECOND');
        $response->assertViewHas('referralCount', 2);
    }

    public function test_guest_cannot_access_referral_page(): void
    {
        $response = $this->get(route('referral'));

        $response->assertRedirect();
    }
}
