<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\CheckProfileSteps;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword123'),
        ]);

        // Create a default profile for the user
        ProviderProfile::create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'test-'.$user->id,
        ]);

        return $user;
    }

    public function test_password_change_with_valid_data_succeeds(): void
    {
        $this->withoutMiddleware(CheckProfileSteps::class);

        $user = $this->createUser();
        $profile = $user->providerProfiles()->first();

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->postJson('/change-password', [
                'current_password' => 'OldPassword123',
                'new_password' => 'NewSecurePass456',
                'new_password_confirmation' => 'NewSecurePass456',
            ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $user->refresh();
        $this->assertTrue(Hash::check('NewSecurePass456', $user->password));
    }

    public function test_password_change_with_wrong_current_password_fails(): void
    {
        $this->withoutMiddleware(CheckProfileSteps::class);

        $user = $this->createUser();
        $profile = $user->providerProfiles()->first();

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->postJson('/change-password', [
                'current_password' => 'WrongPassword',
                'new_password' => 'NewSecurePass456',
                'new_password_confirmation' => 'NewSecurePass456',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['current_password']);

        $user->refresh();
        $this->assertTrue(Hash::check('OldPassword123', $user->password));
    }

    public function test_password_change_requires_confirmation(): void
    {
        $this->withoutMiddleware(CheckProfileSteps::class);

        $user = $this->createUser();
        $profile = $user->providerProfiles()->first();

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->postJson('/change-password', [
                'current_password' => 'OldPassword123',
                'new_password' => 'NewSecurePass456',
                'new_password_confirmation' => 'DifferentPass',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['new_password']);
    }

    public function test_password_change_requires_all_fields(): void
    {
        $this->withoutMiddleware(CheckProfileSteps::class);

        $user = $this->createUser();
        $profile = $user->providerProfiles()->first();

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->postJson('/change-password', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['current_password', 'new_password']);
    }

    public function test_guest_cannot_change_password(): void
    {
        $response = $this->postJson('/change-password', [
            'current_password' => 'OldPassword123',
            'new_password' => 'NewSecurePass456',
            'new_password_confirmation' => 'NewSecurePass456',
        ]);

        $response->assertUnauthorized();
    }

    public function test_password_change_page_is_accessible_to_authenticated_user(): void
    {
        $this->withoutMiddleware(CheckProfileSteps::class);

        $user = $this->createUser();
        $profile = $user->providerProfiles()->first();

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->get('/change-password');

        $response->assertOk();
        $response->assertViewIs('auth.change-password');
    }

    public function test_password_change_is_blocked_when_profile_steps_are_incomplete(): void
    {
        $user = $this->createUser();
        $profile = $user->providerProfiles()->first();

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->postJson('/change-password', [
                'current_password' => 'OldPassword123',
                'new_password' => 'NewSecurePass456',
                'new_password_confirmation' => 'NewSecurePass456',
            ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Please complete your profile first.']);
    }

    public function test_change_email_page_is_blocked_when_profile_steps_are_incomplete(): void
    {
        $user = $this->createUser();
        $profile = $user->providerProfiles()->first();

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->get('/change-email');

        $response->assertRedirect(route('my-profile'));
        $response->assertSessionHas('error', 'Please complete your profile first.');
    }
}
