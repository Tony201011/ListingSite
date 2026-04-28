<?php

namespace Tests\Feature\Profile;

use App\Http\Middleware\CheckProfileSteps;
use App\Http\Middleware\EnsureProfileSelected;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BabeRankControllerTest extends TestCase
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

    public function test_my_babe_rank_view_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAs($user)->get(route('my-babe-rank'));

        $response->assertOk();
        $response->assertViewIs('profile.my-babe-rank');
        $response->assertViewHasAll(['rank', 'profileScore', 'viewsToday', 'shortCode']);
    }

    public function test_babe_rank_read_more_view_is_returned_for_authenticated_provider(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAs($user)->get(route('babe-rank-read-more'));

        $response->assertOk();
        $response->assertViewIs('profile.babe-rank-read-more');
        $response->assertViewHasAll(['rank', 'profileScore', 'viewsToday', 'shortCode']);
    }

    public function test_guest_cannot_access_my_babe_rank(): void
    {
        $response = $this->get(route('my-babe-rank'));

        $response->assertRedirect();
    }

    public function test_guest_cannot_access_babe_rank(): void
    {
        $response = $this->get(route('babe-rank-read-more'));

        $response->assertRedirect();
    }
}
