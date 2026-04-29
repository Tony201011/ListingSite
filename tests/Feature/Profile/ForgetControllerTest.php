<?php

namespace Tests\Feature\Profile;

use App\Actions\GetSetAndForgetState;
use App\Actions\SaveSetAndForget;
use App\Actions\Support\ActionResult;
use App\Http\Middleware\CheckProfileSteps;
use App\Http\Middleware\EnsureProfileSelected;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ForgetControllerTest extends TestCase
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

    /**
     * Act as a provider with their first profile already selected in session.
     */
    private function actingAsProvider(User $user): static
    {
        $profile = $user->providerProfile;

        return $this->actingAs($user)->withSession([
            'active_provider_profile_id' => $profile?->id,
        ]);
    }

    public function test_set_forget_view_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider();

        $getSetAndForgetState = Mockery::mock(GetSetAndForgetState::class);
        $getSetAndForgetState->shouldReceive('execute')
            ->once()
            ->andReturn([
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'online_now_enabled' => false,
                'online_now_days' => [],
                'online_now_time' => '',
                'available_now_enabled' => false,
                'available_now_days' => [],
                'available_now_time' => '',
            ]);

        $this->app->instance(GetSetAndForgetState::class, $getSetAndForgetState);

        $response = $this->actingAsProvider($user)->get(route('set-and-forget'));

        $response->assertOk();
        $response->assertViewIs('profile.set-forget');
    }

    public function test_set_forget_view_passes_state_data(): void
    {
        $user = $this->createProvider();

        $getSetAndForgetState = Mockery::mock(GetSetAndForgetState::class);
        $getSetAndForgetState->shouldReceive('execute')
            ->once()
            ->andReturn([
                'days' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'],
                'online_now_enabled' => true,
                'online_now_days' => ['Monday'],
                'online_now_time' => '08:00',
                'available_now_enabled' => true,
                'available_now_days' => ['Friday'],
                'available_now_time' => '09:00',
            ]);

        $this->app->instance(GetSetAndForgetState::class, $getSetAndForgetState);

        $response = $this->actingAsProvider($user)->get(route('set-and-forget'));

        $response->assertViewHas('online_now_enabled', true);
        $response->assertViewHas('available_now_enabled', true);
        $response->assertViewHas('online_now_time', '08:00');
    }

    public function test_save_stores_settings_and_returns_json(): void
    {
        $user = $this->createProvider();
        $profile = ProviderProfile::where('user_id', $user->id)->first();

        $saveSetAndForget = Mockery::mock(SaveSetAndForget::class);
        $saveSetAndForget->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn ($arg) => $arg instanceof ProviderProfile && $arg->is($profile)),
                Mockery::type('array')
            )
            ->andReturn(ActionResult::success([], 'Set & Forget settings saved successfully.'));

        $this->app->instance(SaveSetAndForget::class, $saveSetAndForget);

        $response = $this->actingAsProvider($user)->postJson(route('set-and-forget.save'), [
            'online_now_enabled' => true,
            'online_now_days' => ['Monday', 'Wednesday'],
            'online_now_time' => '08:00',
            'available_now_enabled' => false,
            'available_now_days' => [],
            'available_now_time' => null,
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Set & Forget settings saved successfully.',
        ]);
    }

    public function test_save_returns_422_when_invalid_day_is_provided(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAsProvider($user)->postJson(route('set-and-forget.save'), [
            'online_now_days' => ['InvalidDay'],
        ]);

        $response->assertStatus(422);
    }

    public function test_save_returns_422_when_time_format_is_invalid(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAsProvider($user)->postJson(route('set-and-forget.save'), [
            'online_now_time' => 'not-a-time',
        ]);

        $response->assertStatus(422);
    }

    public function test_guest_cannot_access_set_forget_page(): void
    {
        $response = $this->get(route('set-and-forget'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_save_set_forget_settings(): void
    {
        $response = $this->postJson(route('set-and-forget.save'), []);

        $response->assertStatus(401);
    }
}
