<?php

namespace Tests\Feature\Profile;

use App\Actions\GetOnlineNowState;
use App\Actions\Support\ActionResult;
use App\Actions\UpdateOnlineNowStatus;
use App\Models\Category;
use App\Models\ProfileImage;
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

    private function markProfileAsComplete(ProviderProfile $profile): void
    {
        $category = Category::query()->firstOrCreate(
            ['slug' => 'test-category'],
            ['name' => 'Test Category']
        );

        $profile->update([
            'introduction_line' => 'Intro',
            'profile_text' => 'Complete profile text',
            'age_group_id' => $category->id,
            'hair_color_id' => $category->id,
            'hair_length_id' => $category->id,
            'ethnicity_id' => $category->id,
            'body_type_id' => $category->id,
            'bust_size_id' => $category->id,
            'your_length_id' => $category->id,
            'availability' => 'available',
            'contact_method' => 'phone',
            'phone_contact_preference' => 'call',
            'time_waster_shield' => 'enabled',
            'primary_identity' => [1],
            'attributes' => [1],
            'services_style' => [1],
            'services_provided' => [1],
        ]);
    }

    private function addProfilePhoto(ProviderProfile $profile): void
    {
        ProfileImage::factory()->create([
            'user_id' => $profile->user_id,
            'provider_profile_id' => $profile->id,
        ]);
    }

    public function test_my_profiles_index_includes_online_states(): void
    {
        [$user, $profiles] = $this->createProviderWithProfiles(2);

        $getOnlineNowState = Mockery::mock(GetOnlineNowState::class);
        $getOnlineNowState->shouldReceive('execute')
            ->twice()
            ->andReturn(['onlineStatus' => false, 'expiresAt' => null]);

        $this->app->instance(GetOnlineNowState::class, $getOnlineNowState);

        $response = $this->actingAs($user)->get(route('profiles.index'));

        $response->assertOk();
        $response->assertViewHas('onlineStates');
    }

    public function test_select_profile_includes_online_states_and_active_profile_id(): void
    {
        [$user, $profiles] = $this->createProviderWithProfiles(2);

        $getOnlineNowState = Mockery::mock(GetOnlineNowState::class);
        $getOnlineNowState->shouldReceive('execute')
            ->twice()
            ->andReturn(['onlineStatus' => true, 'expiresAt' => null]);

        $this->app->instance(GetOnlineNowState::class, $getOnlineNowState);

        $response = $this->actingAs($user)->get(route('select-profile'));

        $response->assertOk();
        $response->assertViewHas('onlineStates');
        $response->assertViewHas('activeProfileId');
    }

    public function test_owner_can_set_profile_online(): void
    {
        [$user, $profiles] = $this->createProviderWithProfiles(2);
        $profile = $profiles[1];
        $this->markProfileAsComplete($profile);
        $this->addProfilePhoto($profile);

        $updateOnlineNowStatus = Mockery::mock(UpdateOnlineNowStatus::class);
        $updateOnlineNowStatus->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn ($p) => $p instanceof ProviderProfile && $p->is($profile)),
                'online'
            )
            ->andReturn(ActionResult::success([
                'status' => 'online',
                'expires_at' => null,
            ], 'Online Now enabled.'));

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

    public function test_owner_cannot_set_profile_online_when_profile_is_incomplete(): void
    {
        [$user, $profiles] = $this->createProviderWithProfiles(1);
        $profile = $profiles[0];

        $response = $this->actingAs($user)->postJson(
            route('profiles.online-status', $profile),
            ['status' => 'online']
        );

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Please complete your profile in Edit Profile and upload at least one photo before going online.',
        ]);
    }

    public function test_owner_cannot_set_profile_online_when_profile_has_no_photo(): void
    {
        [$user, $profiles] = $this->createProviderWithProfiles(1);
        $profile = $profiles[0];
        $this->markProfileAsComplete($profile);

        $response = $this->actingAs($user)->postJson(
            route('profiles.online-status', $profile),
            ['status' => 'online']
        );

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'Please complete your profile in Edit Profile and upload at least one photo before going online.',
        ]);
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
