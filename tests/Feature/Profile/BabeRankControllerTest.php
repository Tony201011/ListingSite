<?php

namespace Tests\Feature\Profile;

use App\Actions\CalculateBabeRank;
use App\Actions\GetActiveProviderProfile;
use App\Http\Middleware\CheckProfileSteps;
use App\Http\Middleware\EnsureProfileSelected;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class BabeRankControllerTest extends TestCase
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

    public function test_my_babe_rank_view_is_returned_for_authenticated_provider(): void
    {
        [$user, $profile] = $this->createProvider();

        $getActiveProviderProfile = Mockery::mock(GetActiveProviderProfile::class);
        $getActiveProviderProfile->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg instanceof User && $arg->is($user)))
            ->andReturn($profile);

        $calculateBabeRank = Mockery::mock(CalculateBabeRank::class);
        $calculateBabeRank->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg instanceof ProviderProfile && $arg->is($profile)))
            ->andReturn([
                'rank' => 50,
                'profileScore' => 60,
                'viewsToday' => 3,
                'shortCode' => 'abc123',
            ]);

        $this->app->instance(GetActiveProviderProfile::class, $getActiveProviderProfile);
        $this->app->instance(CalculateBabeRank::class, $calculateBabeRank);

        $response = $this->actingAs($user)->get(route('my-babe-rank'));

        $response->assertOk();
        $response->assertViewIs('profile.my-babe-rank');
        $response->assertViewHas('rank', 50);
        $response->assertViewHas('profileScore', 60);
        $response->assertViewHas('viewsToday', 3);
        $response->assertViewHas('shortCode', 'abc123');
    }

    public function test_babe_rank_read_more_view_is_returned_for_authenticated_provider(): void
    {
        [$user, $profile] = $this->createProvider();

        $getActiveProviderProfile = Mockery::mock(GetActiveProviderProfile::class);
        $getActiveProviderProfile->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg instanceof User && $arg->is($user)))
            ->andReturn($profile);

        $calculateBabeRank = Mockery::mock(CalculateBabeRank::class);
        $calculateBabeRank->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg instanceof ProviderProfile && $arg->is($profile)))
            ->andReturn([
                'rank' => 40,
                'profileScore' => 55,
                'viewsToday' => 1,
                'shortCode' => null,
            ]);

        $this->app->instance(GetActiveProviderProfile::class, $getActiveProviderProfile);
        $this->app->instance(CalculateBabeRank::class, $calculateBabeRank);

        $response = $this->actingAs($user)->get(route('babe-rank-read-more'));

        $response->assertOk();
        $response->assertViewIs('profile.babe-rank-read-more');
        $response->assertViewHas('rank', 40);
        $response->assertViewHas('profileScore', 55);
        $response->assertViewHas('viewsToday', 1);
        $response->assertViewHas('shortCode', null);
    }

    public function test_babe_rank_uses_active_profile_not_first_profile(): void
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

        $calculateBabeRank = Mockery::mock(CalculateBabeRank::class);
        $calculateBabeRank->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(fn ($arg) => $arg instanceof ProviderProfile && $arg->is($secondProfile)))
            ->andReturn([
                'rank' => 70,
                'profileScore' => 80,
                'viewsToday' => 10,
                'shortCode' => 'xyz789',
            ]);

        $this->app->instance(GetActiveProviderProfile::class, $getActiveProviderProfile);
        $this->app->instance(CalculateBabeRank::class, $calculateBabeRank);

        $response = $this->actingAs($user)->get(route('my-babe-rank'));

        $response->assertOk();
        $response->assertViewHas('rank', 70);
    }

    public function test_guest_cannot_access_my_babe_rank(): void
    {
        $response = $this->get(route('my-babe-rank'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_access_babe_rank(): void
    {
        $response = $this->get(route('babe-rank-read-more'));

        $response->assertRedirect();
    }
}
