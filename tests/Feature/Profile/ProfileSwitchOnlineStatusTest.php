<?php

namespace Tests\Feature\Profile;

use App\Actions\GetOnlineNowState;
use App\Actions\Support\ActionResult;
use App\Actions\UpdateOnlineNowStatus;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ProfileSwitchOnlineStatusTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function createProviderWithProfiles(int $count = 2): array
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profiles = [];
        for ($i = 0; $i < $count; $i++) {
            $profiles[] = ProviderProfile::query()->create([
                'user_id' => $user->id,
                'name' => $user->name.' '.($i + 1),
                'slug' => 'provider-'.$user->id.'-'.$i,
            ]);
        }

        return [$user, $profiles];
    }

    public function test_my_profiles_index_includes_online_states(): void
    {
        [$user, $profiles] = $this->createProviderWithProfiles(2);

        $getOnlineNowState = Mockery::mock(GetOnlineNowState::class);
        $getOnlineNowState->shouldReceive('execute')
            ->twice()
            ->andReturn(['onlineStatus' => false, 'remainingUses' => 4, 'expiresAt' => null]);

        $this->app->instance(GetOnlineNowState::class, $getOnlineNowState);

        $response = $this->actingAs($user)->get(route('profiles.index'));

        $response->assertOk();
        $response->assertViewHas('onlineStates');
    }

    public function test_owner_can_set_profile_online(): void
    {
        [$user, $profiles] = $this->createProviderWithProfiles(2);
        $profile = $profiles[1];

        $updateOnlineNowStatus = Mockery::mock(UpdateOnlineNowStatus::class);
        $updateOnlineNowStatus->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn ($p) => $p instanceof ProviderProfile && $p->is($profile)),
                'online'
            )
            ->andReturn(ActionResult::success([
                'status' => 'online',
                'remaining_uses' => 3,
                'expires_at' => '2026-04-28T10:00:00+00:00',
            ], 'Online Now enabled for 60 minutes.'));

        $this->app->instance(UpdateOnlineNowStatus::class, $updateOnlineNowStatus);

        $response = $this->actingAs($user)->postJson(
            route('profiles.online-status', $profile),
            ['status' => 'online']
        );

        $response->assertOk();
        $response->assertJson(['success' => true, 'status' => 'online']);
    }

    public function test_owner_can_set_profile_offline(): void
    {
        [$user, $profiles] = $this->createProviderWithProfiles(2);
        $profile = $profiles[0];

        $updateOnlineNowStatus = Mockery::mock(UpdateOnlineNowStatus::class);
        $updateOnlineNowStatus->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn ($p) => $p instanceof ProviderProfile && $p->is($profile)),
                'offline'
            )
            ->andReturn(ActionResult::success([
                'status' => 'offline',
                'remaining_uses' => 4,
                'expires_at' => null,
            ], 'Online Now disabled.'));

        $this->app->instance(UpdateOnlineNowStatus::class, $updateOnlineNowStatus);

        $response = $this->actingAs($user)->postJson(
            route('profiles.online-status', $profile),
            ['status' => 'offline']
        );

        $response->assertOk();
        $response->assertJson(['success' => true, 'status' => 'offline']);
    }

    public function test_user_cannot_set_online_status_for_another_users_profile(): void
    {
        [$user] = $this->createProviderWithProfiles(1);
        [$otherUser, $otherProfiles] = $this->createProviderWithProfiles(1);
        $otherProfile = $otherProfiles[0];

        $response = $this->actingAs($user)->postJson(
            route('profiles.online-status', $otherProfile),
            ['status' => 'online']
        );

        $response->assertForbidden();
    }

    public function test_guest_cannot_update_profile_online_status(): void
    {
        [$user, $profiles] = $this->createProviderWithProfiles(1);
        $profile = $profiles[0];

        $response = $this->postJson(
            route('profiles.online-status', $profile),
            ['status' => 'online']
        );

        $response->assertStatus(401);
    }

    public function test_invalid_status_value_returns_422(): void
    {
        [$user, $profiles] = $this->createProviderWithProfiles(1);
        $profile = $profiles[0];

        $response = $this->actingAs($user)->postJson(
            route('profiles.online-status', $profile),
            ['status' => 'maybe']
        );

        $response->assertStatus(422);
    }

    public function test_missing_status_returns_422(): void
    {
        [$user, $profiles] = $this->createProviderWithProfiles(1);
        $profile = $profiles[0];

        $response = $this->actingAs($user)->postJson(
            route('profiles.online-status', $profile),
            []
        );

        $response->assertStatus(422);
    }
}
