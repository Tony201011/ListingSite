<?php

namespace Tests\Feature\Profile;

use App\Actions\GetShowHideProfileState;
use App\Actions\Support\ActionResult;
use App\Actions\UpdateShowHideProfileState;
use App\Http\Middleware\CheckProfileSteps;
use App\Http\Middleware\EnsureProfileSelected;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ShowHideProfileControllerTest extends TestCase
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

    public function test_hide_show_profile_view_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider();

        $getShowHideProfileState = Mockery::mock(GetShowHideProfileState::class);
        $getShowHideProfileState->shouldReceive('execute')
            ->once()
            ->andReturn(['status' => false]);

        $this->app->instance(GetShowHideProfileState::class, $getShowHideProfileState);

        $response = $this->actingAs($user)->get(route('hide-show-profile'));

        $response->assertOk();
        $response->assertViewIs('profile.hide-show');
        $response->assertViewHas('status', false);
    }

    public function test_update_hide_show_profile_returns_json_response(): void
    {
        $user = $this->createProvider();
        $profile = ProviderProfile::where('user_id', $user->id)->first();

        $updateShowHideProfileState = Mockery::mock(UpdateShowHideProfileState::class);
        $updateShowHideProfileState->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn ($arg) => $arg instanceof ProviderProfile && $arg->is($profile)),
                'show'
            )
            ->andReturn(ActionResult::success(['status' => 'show'], 'Your profile is now visible'));

        $this->app->instance(UpdateShowHideProfileState::class, $updateShowHideProfileState);

        $response = $this->actingAs($user)->postJson(route('update-hide-show-profile'), [
            'status' => 'show',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Your profile is now visible',
            'status' => 'show',
        ]);
    }

    public function test_update_hide_show_profile_can_hide_profile(): void
    {
        $user = $this->createProvider();
        $profile = ProviderProfile::where('user_id', $user->id)->first();

        $updateShowHideProfileState = Mockery::mock(UpdateShowHideProfileState::class);
        $updateShowHideProfileState->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn ($arg) => $arg instanceof ProviderProfile && $arg->is($profile)),
                'hide'
            )
            ->andReturn(ActionResult::success(['status' => 'hide'], 'Your profile is now hidden'));

        $this->app->instance(UpdateShowHideProfileState::class, $updateShowHideProfileState);

        $response = $this->actingAs($user)->postJson(route('update-hide-show-profile'), [
            'status' => 'hide',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Your profile is now hidden',
        ]);
    }

    public function test_update_returns_422_when_status_is_missing(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAs($user)->postJson(route('update-hide-show-profile'), []);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonStructure(['errors' => ['status']]);
    }

    public function test_update_returns_422_when_status_is_invalid(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAs($user)->postJson(route('update-hide-show-profile'), [
            'status' => 'invalid-value',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['status']]);
    }

    public function test_guest_cannot_access_hide_show_profile_view(): void
    {
        $response = $this->get(route('hide-show-profile'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_update_hide_show_profile(): void
    {
        $response = $this->postJson(route('update-hide-show-profile'), [
            'status' => 'show',
        ]);

        $response->assertStatus(401);
    }
}
