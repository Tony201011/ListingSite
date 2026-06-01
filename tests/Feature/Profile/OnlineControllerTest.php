<?php

namespace Tests\Feature\Profile;

use App\Actions\GetOnlineNowState;
use App\Actions\Support\ActionResult;
use App\Actions\UpdateOnlineNowStatus;
use App\Http\Middleware\CheckProfileSteps;
use App\Http\Middleware\EnsureProfileSelected;
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

    public function test_online_now_view_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider();

        $getOnlineNowState = Mockery::mock(GetOnlineNowState::class);
        $getOnlineNowState->shouldReceive('execute')
            ->once()
            ->andReturn([
                'onlineStatus' => false,
                'expiresAt' => null,
                'blockedBalance' => false,
            ]);

        $this->app->instance(GetOnlineNowState::class, $getOnlineNowState);

        $response = $this->actingAs($user)->get(route('online-now'));

        $response->assertOk();
        $response->assertViewIs('profile.online-now');
        $response->assertViewHas('onlineStatus', false);
        $response->assertSeeText('Mark yourself available for online enquiries and improve visibility.');
    }

    public function test_update_status_to_online_returns_json_response(): void
    {
        $user = $this->createProvider();
        $profile = ProviderProfile::where('user_id', $user->id)->first();

        $updateOnlineNowStatus = Mockery::mock(UpdateOnlineNowStatus::class);
        $updateOnlineNowStatus->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn ($arg) => $arg instanceof ProviderProfile && $arg->is($profile)),
                'online'
            )
            ->andReturn(ActionResult::success([
                'status' => 'online',
                'expires_at' => null,
            ], 'Online Now enabled.'));

        $this->app->instance(UpdateOnlineNowStatus::class, $updateOnlineNowStatus);

        $response = $this->actingAs($user)->postJson(route('online.update-status'), [
            'status' => 'online',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Online Now enabled.',
            'status' => 'online',
        ]);
    }

    public function test_update_status_to_online_returns_no_expiry(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAs($user)->postJson(route('online.update-status'), [
            'status' => 'online',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('status', 'online');
        $response->assertJsonPath('message', 'Online Now enabled.');
        $response->assertJsonPath('expires_at', null);
    }

    public function test_update_status_to_offline_returns_json_response(): void
    {
        $user = $this->createProvider();
        $profile = ProviderProfile::where('user_id', $user->id)->first();

        $updateOnlineNowStatus = Mockery::mock(UpdateOnlineNowStatus::class);
        $updateOnlineNowStatus->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn ($arg) => $arg instanceof ProviderProfile && $arg->is($profile)),
                'offline'
            )
            ->andReturn(ActionResult::success([
                'status' => 'offline',
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

    public function test_update_status_to_online_is_blocked_when_free_listing_expired_and_balance_negative(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
            'free_listing_expires_at' => now()->subDay(),
            'credits' => -1,
        ]);

        $response = $this->actingAs($user)->postJson(route('online.update-status'), [
            'status' => 'online',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonFragment([
            'message' => 'Your 21-day period has expired and this profile balance is negative. Please top up this profile to go online or become available now.',
        ]);
    }

    public function test_update_status_to_online_is_allowed_when_free_listing_expired_but_balance_non_negative(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
            'free_listing_expires_at' => now()->subDay(),
            'credits' => 0,
        ]);

        $response = $this->actingAs($user)->postJson(route('online.update-status'), [
            'status' => 'online',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('status', 'online');
    }

    public function test_update_status_to_online_is_allowed_when_free_listing_active_and_balance_negative(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
            'free_listing_expires_at' => now()->addDays(10),
            'credits' => -5,
        ]);

        $response = $this->actingAs($user)->postJson(route('online.update-status'), [
            'status' => 'online',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('status', 'online');
    }

    public function test_update_status_to_offline_is_always_allowed_even_when_free_listing_expired_and_balance_negative(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
            'free_listing_expires_at' => now()->subDay(),
            'credits' => -1,
        ]);

        $response = $this->actingAs($user)->postJson(route('online.update-status'), [
            'status' => 'offline',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('status', 'offline');
    }
}
