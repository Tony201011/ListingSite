<?php

namespace Tests\Feature\Profile;

use App\Actions\GetProfileSettingPageData;
use App\Http\Middleware\CheckProfileSteps;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class ProfileSettingControllerTest extends TestCase
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

    public function test_profile_setting_view_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider();

        $getProfileSettingPageData = Mockery::mock(GetProfileSettingPageData::class);
        $getProfileSettingPageData->shouldReceive('execute')
            ->once()
            ->andReturn([
                'profileImages' => collect(),
                'videos' => collect(),
                'photoVerification' => false,
                'userInfo' => ['user' => $user],
            ]);

        $this->app->instance(GetProfileSettingPageData::class, $getProfileSettingPageData);

        $response = $this->actingAs($user)->get(route('profile-setting'));

        $response->assertOk();
        $response->assertViewIs('profile.profile-setting');
    }

    public function test_profile_setting_view_passes_data_from_action(): void
    {
        $user = $this->createProvider();

        $viewData = [
            'profileImages' => collect(),
            'videos' => collect(),
            'photoVerification' => true,
            'userInfo' => ['user' => $user],
        ];

        $getProfileSettingPageData = Mockery::mock(GetProfileSettingPageData::class);
        $getProfileSettingPageData->shouldReceive('execute')
            ->once()
            ->andReturn($viewData);

        $this->app->instance(GetProfileSettingPageData::class, $getProfileSettingPageData);

        $response = $this->actingAs($user)->get(route('profile-setting'));

        $response->assertOk();
        $response->assertViewHas('photoVerification', true);
    }

    public function test_guest_cannot_access_profile_setting(): void
    {
        $response = $this->get(route('profile-setting'));

        $response->assertRedirect();
    }
}
