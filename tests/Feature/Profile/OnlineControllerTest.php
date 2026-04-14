<?php

namespace Tests\Feature\Profile;

use App\Actions\GetOnlineNowState;
use App\Actions\Support\ActionResult;
use App\Actions\UpdateOnlineNowStatus;
use App\Http\Middleware\CheckProfileSteps;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class OnlineControllerTest extends TestCase
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

    public function test_online_now_view_is_returned_for_authenticated_provider(): void
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

        $this->app->instance(GetOnlineNowState::class, $getOnlineNowState);

        $response = $this->actingAs($user)->get(route('online-now'));

        $response->assertOk();
        $response->assertViewIs('profile.online-now');
        $response->assertViewHas('onlineStatus', false);
        $response->assertViewHas('remainingUses', 4);
    }

    public function test_update_status_to_online_returns_json_response(): void
    {
        $user = $this->createProvider();

        $updateOnlineNowStatus = Mockery::mock(UpdateOnlineNowStatus::class);
        $updateOnlineNowStatus->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn ($arg) => $arg->is($user)),
                'online'
            )
            ->andReturn(ActionResult::success([
                'status' => 'online',
                'remaining_uses' => 3,
                'expires_at' => '2026-04-14T03:50:22+00:00',
            ], 'Online Now enabled for 60 minutes.'));

        $this->app->instance(UpdateOnlineNowStatus::class, $updateOnlineNowStatus);

        $response = $this->actingAs($user)->postJson(route('online.update-status'), [
            'status' => 'online',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Online Now enabled for 60 minutes.',
            'status' => 'online',
        ]);
    }

    public function test_update_status_to_offline_returns_json_response(): void
    {
        $user = $this->createProvider();

        $updateOnlineNowStatus = Mockery::mock(UpdateOnlineNowStatus::class);
        $updateOnlineNowStatus->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn ($arg) => $arg->is($user)),
                'offline'
            )
            ->andReturn(ActionResult::success([
                'status' => 'offline',
                'remaining_uses' => 4,
                'expires_at' => null,
            ], 'Online Now disabled.'));

        $this->app->instance(UpdateOnlineNowStatus::class, $updateOnlineNowStatus);

        $response = $this->actingAs($user)->postJson(route('online.update-status'), [
            'status' => 'offline',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Online Now disabled.',
            'status' => 'offline',
        ]);
    }

    public function test_update_status_returns_422_when_status_is_missing(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAs($user)->postJson(route('online.update-status'), []);

        $response->assertStatus(422);
    }

    public function test_update_status_returns_422_when_status_is_invalid(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAs($user)->postJson(route('online.update-status'), [
            'status' => 'maybe',
        ]);

        $response->assertStatus(422);
    }

    public function test_guest_cannot_access_online_now_view(): void
    {
        $response = $this->get(route('online-now'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_update_online_status(): void
    {
        $response = $this->postJson(route('online.update-status'), [
            'status' => 'online',
        ]);

        $response->assertStatus(401);
    }
}
