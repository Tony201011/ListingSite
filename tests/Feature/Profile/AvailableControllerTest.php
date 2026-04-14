<?php

namespace Tests\Feature\Profile;

use App\Actions\GetAvailableNowState;
use App\Actions\Support\ActionResult;
use App\Actions\UpdateAvailableNowStatus;
use App\Http\Middleware\CheckProfileSteps;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AvailableControllerTest extends TestCase
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

    public function test_available_now_view_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider();

        $getAvailableNowState = Mockery::mock(GetAvailableNowState::class);
        $getAvailableNowState->shouldReceive('execute')
            ->once()
            ->andReturn([
                'status' => false,
                'remainingUses' => 2,
                'expiresAt' => null,
            ]);

        $this->app->instance(GetAvailableNowState::class, $getAvailableNowState);

        $response = $this->actingAs($user)->get(route('available-now'));

        $response->assertOk();
        $response->assertViewIs('profile.available-now');
        $response->assertViewHas('status', false);
        $response->assertViewHas('remainingUses', 2);
    }

    public function test_update_status_to_online_returns_json_response(): void
    {
        $user = $this->createProvider();

        $updateAvailableNowStatus = Mockery::mock(UpdateAvailableNowStatus::class);
        $updateAvailableNowStatus->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn ($arg) => $arg->is($user)),
                'online'
            )
            ->andReturn(ActionResult::success([
                'status' => 'online',
                'remaining_uses' => 1,
                'expires_at' => '2026-04-14T05:50:22+00:00',
            ], 'You are now available for enquiries for 2 hours.'));

        $this->app->instance(UpdateAvailableNowStatus::class, $updateAvailableNowStatus);

        $response = $this->actingAs($user)->postJson(route('available.update-status'), [
            'status' => 'online',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'You are now available for enquiries for 2 hours.',
            'status' => 'online',
        ]);
    }

    public function test_update_status_to_offline_returns_json_response(): void
    {
        $user = $this->createProvider();

        $updateAvailableNowStatus = Mockery::mock(UpdateAvailableNowStatus::class);
        $updateAvailableNowStatus->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn ($arg) => $arg->is($user)),
                'offline'
            )
            ->andReturn(ActionResult::success([
                'status' => 'offline',
                'remaining_uses' => 2,
                'expires_at' => null,
            ], 'You are now unavailable.'));

        $this->app->instance(UpdateAvailableNowStatus::class, $updateAvailableNowStatus);

        $response = $this->actingAs($user)->postJson(route('available.update-status'), [
            'status' => 'offline',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'You are now unavailable.',
        ]);
    }

    public function test_update_status_returns_422_when_status_is_missing(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAs($user)->postJson(route('available.update-status'), []);

        $response->assertStatus(422);
    }

    public function test_update_status_returns_422_when_status_is_invalid(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAs($user)->postJson(route('available.update-status'), [
            'status' => 'maybe',
        ]);

        $response->assertStatus(422);
    }

    public function test_guest_cannot_access_available_now_view(): void
    {
        $response = $this->get(route('available-now'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_update_available_status(): void
    {
        $response = $this->postJson(route('available.update-status'), [
            'status' => 'online',
        ]);

        $response->assertStatus(401);
    }
}
