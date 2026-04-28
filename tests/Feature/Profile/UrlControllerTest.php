<?php

namespace Tests\Feature\Profile;

use App\Actions\GetShortUrlPageData;
use App\Actions\Support\ActionResult;
use App\Actions\UpdateUserShortUrl;
use App\Http\Middleware\CheckProfileSteps;
use App\Http\Middleware\EnsureProfileSelected;
use App\Models\ProviderProfile;
use App\Models\ShortUrl;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class UrlControllerTest extends TestCase
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

    public function test_short_url_view_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider();

        $getShortUrlPageData = Mockery::mock(GetShortUrlPageData::class);
        $getShortUrlPageData->shouldReceive('execute')
            ->once()
            ->andReturn([
                'slug' => 'my-custom-url',
                'siteSetting' => true,
            ]);

        $this->app->instance(GetShortUrlPageData::class, $getShortUrlPageData);

        $response = $this->actingAs($user)->get(route('short-url'));

        $response->assertOk();
        $response->assertViewIs('profile.short-url');
        $response->assertViewHas('slug', 'my-custom-url');
    }

    public function test_short_url_redirects_when_action_returns_redirect(): void
    {
        $user = $this->createProvider();

        $getShortUrlPageData = Mockery::mock(GetShortUrlPageData::class);
        $getShortUrlPageData->shouldReceive('execute')
            ->once()
            ->andReturn([
                'redirect' => '/signin',
            ]);

        $this->app->instance(GetShortUrlPageData::class, $getShortUrlPageData);

        $response = $this->actingAs($user)->get(route('short-url'));

        $response->assertRedirect('/signin');
    }

    public function test_update_short_url_returns_json_response(): void
    {
        $user = $this->createProvider();
        $profile = ProviderProfile::where('user_id', $user->id)->first();

        $updateUserShortUrl = Mockery::mock(UpdateUserShortUrl::class);
        $updateUserShortUrl->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn ($arg) => $arg instanceof ProviderProfile && $arg->is($profile)),
                'my-new-slug'
            )
            ->andReturn(ActionResult::success([
                'slug' => 'my-new-slug',
            ], 'Short URL updated successfully.'));

        $this->app->instance(UpdateUserShortUrl::class, $updateUserShortUrl);

        $response = $this->actingAs($user)->postJson(route('short-url.update'), [
            'slug' => 'my-new-slug',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Short URL updated successfully.',
            'slug' => 'my-new-slug',
        ]);
    }

    public function test_update_short_url_returns_422_when_slug_is_missing(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAs($user)->postJson(route('short-url.update'), []);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['slug']]);
    }

    public function test_update_short_url_returns_422_when_slug_has_invalid_characters(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAs($user)->postJson(route('short-url.update'), [
            'slug' => 'invalid slug with spaces!',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['slug']]);
    }

    public function test_update_short_url_returns_422_when_slug_is_taken_by_another_user(): void
    {
        $user = $this->createProvider();
        $otherUser = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $otherProfile = ProviderProfile::query()->create([
            'user_id' => $otherUser->id,
            'name' => $otherUser->name,
            'slug' => 'provider-'.$otherUser->id,
        ]);

        ShortUrl::query()->create([
            'provider_profile_id' => $otherProfile->id,
            'short_url' => 'taken-slug',
        ]);

        $response = $this->actingAs($user)->postJson(route('short-url.update'), [
            'slug' => 'taken-slug',
        ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['errors' => ['slug']]);
    }

    public function test_guest_cannot_access_short_url_page(): void
    {
        $response = $this->get(route('short-url'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_update_short_url(): void
    {
        $response = $this->postJson(route('short-url.update'), [
            'slug' => 'some-slug',
        ]);

        $response->assertStatus(401);
    }
}
