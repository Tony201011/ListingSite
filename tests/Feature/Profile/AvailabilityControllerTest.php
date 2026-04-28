<?php

namespace Tests\Feature\Profile;

use App\Actions\GetUserAvailability;
use App\Actions\UpdateUserAvailability;
use App\Http\Middleware\CheckProfileSteps;
use App\Http\Middleware\EnsureProfileSelected;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AvailabilityControllerTest extends TestCase
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

    public function test_edit_view_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider();
        $profile = ProviderProfile::where('user_id', $user->id)->first();

        $getUserAvailability = Mockery::mock(GetUserAvailability::class);
        $getUserAvailability->shouldReceive('forEdit')
            ->once()
            ->with($profile->id)
            ->andReturn(collect());
        $getUserAvailability->shouldReceive('days')
            ->once()
            ->andReturn(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday']);

        $this->app->instance(GetUserAvailability::class, $getUserAvailability);

        $response = $this->actingAs($user)->get(route('availability.edit'));

        $response->assertOk();
        $response->assertViewIs('profile.set-your-availability');
        $response->assertViewHas('days');
        $response->assertViewHas('saved');
    }

    public function test_show_view_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider();
        $profile = ProviderProfile::where('user_id', $user->id)->first();

        $getUserAvailability = Mockery::mock(GetUserAvailability::class);
        $getUserAvailability->shouldReceive('forShow')
            ->once()
            ->with($profile->id)
            ->andReturn([
                'availabilities' => collect(),
                'availabilityCount' => 0,
            ]);

        $this->app->instance(GetUserAvailability::class, $getUserAvailability);

        $response = $this->actingAs($user)->get(route('availability.show'));

        $response->assertOk();
        $response->assertViewIs('profile.my-availability');
    }

    public function test_update_returns_json_on_ajax_request(): void
    {
        $user = $this->createProvider();
        $profile = ProviderProfile::where('user_id', $user->id)->first();

        $updateUserAvailability = Mockery::mock(UpdateUserAvailability::class);
        $updateUserAvailability->shouldReceive('execute')
            ->once()
            ->with($profile->id, Mockery::type('array'));

        $this->app->instance(UpdateUserAvailability::class, $updateUserAvailability);

        $response = $this->actingAs($user)->postJson(route('availability.update'), [
            'availability' => [
                'Monday' => [
                    'enabled' => true,
                    'all_day' => true,
                ],
            ],
        ]);

        $response->assertOk();
        $response->assertJson([
            'status' => true,
            'message' => 'Availability updated successfully.',
        ]);
    }

    public function test_update_redirects_on_non_ajax_request(): void
    {
        $user = $this->createProvider();

        $updateUserAvailability = Mockery::mock(UpdateUserAvailability::class);
        $updateUserAvailability->shouldReceive('execute')
            ->once();

        $this->app->instance(UpdateUserAvailability::class, $updateUserAvailability);

        $response = $this->actingAs($user)->post(route('availability.update'), [
            'availability' => [],
        ]);

        $response->assertRedirect(route('availability.edit'));
        $response->assertSessionHas('success', 'Availability updated successfully.');
    }

    public function test_update_returns_422_when_invalid_day_is_provided(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAs($user)->postJson(route('availability.update'), [
            'availability' => [
                'InvalidDay' => [
                    'enabled' => true,
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_update_returns_422_when_from_time_format_is_invalid(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAs($user)->postJson(route('availability.update'), [
            'availability' => [
                'Monday' => [
                    'enabled' => true,
                    'from' => 'not-a-time',
                    'to' => '18:00',
                ],
            ],
        ]);

        $response->assertStatus(422);
    }

    public function test_guest_cannot_access_availability_edit(): void
    {
        $response = $this->get(route('availability.edit'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_update_availability(): void
    {
        $response = $this->postJson(route('availability.update'), []);

        $response->assertStatus(401);
    }

    public function test_guest_cannot_view_my_availability(): void
    {
        $response = $this->get(route('availability.show'));

        $response->assertRedirect();
    }
}
