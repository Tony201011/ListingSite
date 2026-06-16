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

    protected function setUp(): void
    {
        parent::setUp();

        // Skip tests if the required controller doesn't exist
        if (!class_exists('App\Http\Controllers\Frontend\ProviderRegisterController') &&
            !class_exists('App\Http\Controllers\Auth\ProviderRegisterController')) {
            $this->markTestSkipped('Required password change controller not found');
        }
    }

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
        $user = $this->createUser();

        $response = $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($user)
            ->post(route('change-password.update'), [
                'current_password' => 'OldPassword123',
                'new_password' => 'NewPassword456!',
                'new_password_confirmation' => 'NewPassword456!',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertTrue(Hash::check('NewPassword456!', $user->fresh()->password));
    }

    public function test_password_change_with_wrong_current_password_fails(): void
    {
        $user = $this->createUser();

        $response = $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($user)
            ->post(route('change-password.update'), [
                'current_password' => 'WrongPassword999',
                'new_password' => 'NewPassword456!',
                'new_password_confirmation' => 'NewPassword456!',
            ]);

        $response->assertSessionHasErrors('current_password');
        $this->assertTrue(Hash::check('OldPassword123', $user->fresh()->password));
    }

    public function test_password_change_requires_confirmation(): void
    {
        $user = $this->createUser();

        $response = $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($user)
            ->post(route('change-password.update'), [
                'current_password' => 'OldPassword123',
                'new_password' => 'NewPassword456!',
                'new_password_confirmation' => 'DifferentPassword789!',
            ]);

        $response->assertSessionHasErrors('new_password');
        $this->assertTrue(Hash::check('OldPassword123', $user->fresh()->password));
    }

    public function test_password_change_requires_all_fields(): void
    {
        $user = $this->createUser();

        $response = $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($user)
            ->post(route('change-password.update'), []);

        $response->assertSessionHasErrors(['current_password', 'new_password']);
    }

    public function test_guest_cannot_change_password(): void
    {
        $response = $this->post(route('change-password.update'), [
            'current_password' => 'OldPassword123',
            'new_password' => 'NewPassword456!',
            'new_password_confirmation' => 'NewPassword456!',
        ]);

        $response->assertRedirect(route('signin'));
    }

    public function test_password_change_page_is_accessible_to_authenticated_user(): void
    {
        $user = $this->createUser();

        $response = $this->withoutMiddleware(CheckProfileSteps::class)
            ->actingAs($user)
            ->get(route('change-password'));

        $response->assertOk();
        $response->assertViewIs('auth.change-password');
    }

    public function test_password_change_is_blocked_when_profile_steps_are_incomplete(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get(route('change-password'));

        $response->assertRedirect(route('my-profile'));
        $response->assertSessionHas('error', 'Please complete your profile first.');
    }

    public function test_change_email_page_is_blocked_when_profile_steps_are_incomplete(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)
            ->get(route('change-email'));

        $response->assertRedirect(route('my-profile'));
        $response->assertSessionHas('error', 'Please complete your profile first.');
    }
}
