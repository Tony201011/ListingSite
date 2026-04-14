<?php

namespace Tests\Feature\Profile;

use App\Actions\GetProfileMessage;
use App\Actions\SaveProfileMessage;
use App\Actions\Support\ActionResult;
use App\Http\Middleware\CheckProfileSteps;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ProfileMessageControllerTest extends TestCase
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

    public function test_profile_message_view_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider();

        $getProfileMessage = Mockery::mock(GetProfileMessage::class);
        $getProfileMessage->shouldReceive('execute')
            ->once()
            ->andReturn('My existing message');

        $this->app->instance(GetProfileMessage::class, $getProfileMessage);

        $response = $this->actingAs($user)->get(route('profile-message'));

        $response->assertOk();
        $response->assertViewIs('profile.profile-message');
        $response->assertViewHas('profileMessage', 'My existing message');
    }

    public function test_profile_message_view_shows_null_when_no_message_exists(): void
    {
        $user = $this->createProvider();

        $getProfileMessage = Mockery::mock(GetProfileMessage::class);
        $getProfileMessage->shouldReceive('execute')
            ->once()
            ->andReturn(null);

        $this->app->instance(GetProfileMessage::class, $getProfileMessage);

        $response = $this->actingAs($user)->get(route('profile-message'));

        $response->assertOk();
        $response->assertViewHas('profileMessage', null);
    }

    public function test_store_saves_profile_message_and_returns_json(): void
    {
        $user = $this->createProvider();

        $saveProfileMessage = Mockery::mock(SaveProfileMessage::class);
        $saveProfileMessage->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn ($arg) => $arg->is($user)),
                'Hello, welcome to my profile!'
            )
            ->andReturn(ActionResult::success([], 'Profile message saved successfully.'));

        $this->app->instance(SaveProfileMessage::class, $saveProfileMessage);

        $response = $this->actingAs($user)->postJson(route('profile-message.store'), [
            'message' => 'Hello, welcome to my profile!',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Profile message saved successfully.',
        ]);
    }

    public function test_store_returns_422_when_message_is_missing(): void
    {
        $user = $this->createProvider();

        $saveProfileMessage = Mockery::mock(SaveProfileMessage::class);
        $saveProfileMessage->shouldNotReceive('execute');

        $this->app->instance(SaveProfileMessage::class, $saveProfileMessage);

        $response = $this->actingAs($user)->postJson(route('profile-message.store'), [
            'message' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonStructure(['errors' => ['message']]);
    }

    public function test_store_returns_422_when_message_exceeds_max_length(): void
    {
        $user = $this->createProvider();

        $saveProfileMessage = Mockery::mock(SaveProfileMessage::class);
        $saveProfileMessage->shouldNotReceive('execute');

        $this->app->instance(SaveProfileMessage::class, $saveProfileMessage);

        $response = $this->actingAs($user)->postJson(route('profile-message.store'), [
            'message' => str_repeat('a', 10001),
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['message']]);
    }

    public function test_guest_cannot_access_profile_message_view(): void
    {
        $response = $this->get(route('profile-message'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_store_profile_message(): void
    {
        $response = $this->postJson(route('profile-message.store'), [
            'message' => 'Hello',
        ]);

        $response->assertStatus(401);
    }
}
