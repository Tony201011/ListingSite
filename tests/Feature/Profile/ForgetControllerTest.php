<?php

namespace Tests\Feature\Profile;

use App\Http\Middleware\CheckProfileSteps;
use App\Http\Middleware\EnsureProfileSelected;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ForgetControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([CheckProfileSteps::class, EnsureProfileSelected::class]);
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

    public function test_set_forget_view_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAs($user)->get(route('set-and-forget'));

        $response->assertOk();
        $response->assertViewIs('profile.set-forget');
    }

    public function test_guest_cannot_access_set_forget_page(): void
    {
        $response = $this->get(route('set-and-forget'));

        $response->assertRedirect();
    }
}
