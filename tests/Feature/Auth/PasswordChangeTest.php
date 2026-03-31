<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(): User
    {
        return User::factory()->create([
            'password' => Hash::make('OldPassword123'),
        ]);
    }

    public function test_password_change_with_valid_data_succeeds(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->postJson('/change-password', [
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
        $user = $this->createUser();

        $response = $this->actingAs($user)->postJson('/change-password', [
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
        $user = $this->createUser();

        $response = $this->actingAs($user)->postJson('/change-password', [
            'current_password' => 'OldPassword123',
            'new_password' => 'NewSecurePass456',
            'new_password_confirmation' => 'DifferentPass',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['new_password']);
    }

    public function test_password_change_requires_all_fields(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->postJson('/change-password', []);

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
        $user = $this->createUser();

        $response = $this->actingAs($user)->get('/change-password');

        $response->assertOk();
        $response->assertViewIs('auth.change-password');
    }
}
