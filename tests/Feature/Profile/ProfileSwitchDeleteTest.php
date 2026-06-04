<?php

namespace Tests\Feature\Profile;

use App\Actions\GetOnlineNowState;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ProfileSwitchDeleteTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_owner_can_delete_selected_profiles(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profileOne = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name.' One',
            'slug' => 'provider-'.$user->id.'-one',
        ]);
        $profileTwo = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name.' Two',
            'slug' => 'provider-'.$user->id.'-two',
        ]);
        $profileThree = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name.' Three',
            'slug' => 'provider-'.$user->id.'-three',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profileTwo->id])
            ->delete(route('profiles.destroy-selected'), [
                'profile_ids' => [$profileOne->id, $profileTwo->id],
            ]);

        $response->assertRedirect(route('profiles.index'));
        $response->assertSessionHas('success', '2 selected profiles deleted.');
        $response->assertSessionHas('active_provider_profile_id', $profileThree->id);
        $this->assertSoftDeleted('provider_profiles', ['id' => $profileOne->id]);
        $this->assertSoftDeleted('provider_profiles', ['id' => $profileTwo->id]);
        $this->assertDatabaseHas('provider_profiles', ['id' => $profileThree->id, 'deleted_at' => null]);
    }

    public function test_owner_cannot_delete_all_profiles_using_selected_delete(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profileOne = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name.' One',
            'slug' => 'provider-'.$user->id.'-one',
        ]);
        $profileTwo = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name.' Two',
            'slug' => 'provider-'.$user->id.'-two',
        ]);

        $response = $this->actingAs($user)
            ->from(route('profiles.index'))
            ->delete(route('profiles.destroy-selected'), [
                'profile_ids' => [$profileOne->id, $profileTwo->id],
            ]);

        $response->assertRedirect(route('profiles.index'));
        $response->assertSessionHasErrors('profile_ids');
        $this->assertDatabaseHas('provider_profiles', ['id' => $profileOne->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('provider_profiles', ['id' => $profileTwo->id, 'deleted_at' => null]);
    }

    public function test_owner_cannot_delete_other_users_profiles_using_selected_delete(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $ownProfile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name.' One',
            'slug' => 'provider-'.$user->id.'-one',
        ]);

        $otherUser = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $otherProfile = ProviderProfile::query()->create([
            'user_id' => $otherUser->id,
            'name' => $otherUser->name.' One',
            'slug' => 'provider-'.$otherUser->id.'-one',
        ]);

        $response = $this->actingAs($user)
            ->from(route('profiles.index'))
            ->delete(route('profiles.destroy-selected'), [
                'profile_ids' => [$ownProfile->id, $otherProfile->id],
            ]);

        $response->assertRedirect(route('profiles.index'));
        $response->assertSessionHasErrors('profile_ids');
        $this->assertDatabaseHas('provider_profiles', ['id' => $ownProfile->id, 'deleted_at' => null]);
        $this->assertDatabaseHas('provider_profiles', ['id' => $otherProfile->id, 'deleted_at' => null]);
    }

    public function test_single_remaining_profile_still_shows_individual_delete_button(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name.' One',
            'slug' => 'provider-'.$user->id.'-one',
        ]);

        $getOnlineNowState = Mockery::mock(GetOnlineNowState::class);
        $getOnlineNowState->shouldReceive('execute')
            ->once()
            ->andReturn(['onlineStatus' => false, 'expiresAt' => null]);

        $this->app->instance(GetOnlineNowState::class, $getOnlineNowState);

        $response = $this->actingAs($user)->get(route('profiles.index'));

        $response->assertOk();
        $response->assertSee(route('profiles.destroy', $profile), false);
        $response->assertDontSee('delete-selected-profiles-form', false);
    }

    public function test_owner_can_switch_profile_using_get_route(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profileOne = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name.' One',
            'slug' => 'provider-'.$user->id.'-one',
        ]);
        $profileTwo = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name.' Two',
            'slug' => 'provider-'.$user->id.'-two',
        ]);

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profileOne->id])
            ->get(route('profiles.switch', $profileTwo));

        $response->assertRedirect(route('my-profile'));
        $response->assertSessionHas('active_provider_profile_id', $profileTwo->id);
    }

    public function test_store_creates_approved_profile(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $response = $this->actingAs($user)->post(route('profiles.store'), [
            'name' => 'S8www811w',
            'phone' => '0400000000',
        ]);

        $response->assertRedirect(route('edit-profile'));
        $response->assertSessionHas('success', 'New profile created. Please fill in your profile details.');
        $response->assertSessionHas('profile_form_heading', 'create');

        $this->assertDatabaseHas('provider_profiles', [
            'user_id' => $user->id,
            'name' => 'S8www811w',
            'slug' => 's8www811w',
            'profile_status' => 'approved',
        ]);
    }

    public function test_switch_to_edit_sets_edit_heading_flag(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name.' One',
            'slug' => 'provider-'.$user->id.'-one',
        ]);

        $response = $this->actingAs($user)
            ->post(route('profiles.switch-edit', $profile));

        $response->assertRedirect(route('edit-profile'));
        $response->assertSessionHas('active_provider_profile_id', $profile->id);
        $response->assertSessionHas('profile_form_heading', 'edit');
    }
}
