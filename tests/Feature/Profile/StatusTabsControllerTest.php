<?php

namespace Tests\Feature\Profile;

use App\Actions\GetAvailableNowState;
use App\Actions\GetOnlineNowState;
use App\Actions\GetShowHideProfileState;
use App\Http\Middleware\CheckProfileSteps;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class StatusTabsControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(CheckProfileSteps::class);
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

    public function test_status_view_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider();

        $getOnlineNowState = Mockery::mock(GetOnlineNowState::class);
        $getOnlineNowState->shouldReceive('execute')
            ->once()
            ->andReturn([
                'onlineStatus' => false,
                'remainingUses' => 4,
                'expiresAt' => null,
            ]);

        $getAvailableNowState = Mockery::mock(GetAvailableNowState::class);
        $getAvailableNowState->shouldReceive('execute')
            ->once()
            ->andReturn([
                'status' => false,
                'remainingUses' => 2,
                'expiresAt' => null,
            ]);

        $getShowHideProfileState = Mockery::mock(GetShowHideProfileState::class);
        $getShowHideProfileState->shouldReceive('execute')
            ->once()
            ->andReturn([
                'status' => true,
            ]);

        $this->app->instance(GetOnlineNowState::class, $getOnlineNowState);
        $this->app->instance(GetAvailableNowState::class, $getAvailableNowState);
        $this->app->instance(GetShowHideProfileState::class, $getShowHideProfileState);

        $response = $this->actingAs($user)->get(route('status'));

        $response->assertOk();
        $response->assertViewIs('profile.status-tabs');
        $response->assertViewHas('onlineStatus', false);
        $response->assertViewHas('onlineRemainingUses', 4);
        $response->assertViewHas('onlineExpiresAt', null);
        $response->assertViewHas('availableStatus', false);
        $response->assertViewHas('availableRemainingUses', 2);
        $response->assertViewHas('availableExpiresAt', null);
        $response->assertViewHas('visibilityStatus', true);
    }

    public function test_guest_cannot_access_status_page(): void
    {
        $response = $this->get(route('status'));

        $response->assertRedirect();
    }
}
