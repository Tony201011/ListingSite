<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginLogoutTest extends TestCase
{
    use RefreshDatabase;

    private function createVerifiedUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'email_verified_at' => now(),
            'is_blocked' => false,
            'password' => Hash::make('CorrectPass123'),
        ], $overrides));
    }

    // ---------------------------------------------------------------
    // Login success
    // ---------------------------------------------------------------

    public function test_login_with_valid_credentials_redirects_to_select_profile(): void
    {
        $user = $this->createVerifiedUser();

        $response = $this->from('/signin')->post('/signin', [
            'email' => $user->email,
            'password' => 'CorrectPass123',
        ]);

        $response->assertRedirect('/select-profile');
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_regenerates_session(): void
    {
        $user = $this->createVerifiedUser();
        $oldSessionId = session()->getId();

        $this->from('/signin')->post('/signin', [
            'email' => $user->email,
            'password' => 'CorrectPass123',
        ]);

        $this->assertNotEquals($oldSessionId, session()->getId());
    }

    // ---------------------------------------------------------------
    // Login failure scenarios
    // ---------------------------------------------------------------

    public function test_login_with_wrong_password_returns_error(): void
    {
        $user = $this->createVerifiedUser();

        $response = $this->from('/signin')->post('/signin', [
            'email' => $user->email,
            'password' => 'WrongPassword',
        ]);

        $response->assertRedirect('/signin');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_login_with_nonexistent_email_returns_error(): void
    {
        $response = $this->from('/signin')->post('/signin', [
            'email' => 'nobody@example.com',
            'password' => 'SomePassword',
        ]);

        $response->assertRedirect('/signin');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_login_with_blocked_account_returns_error(): void
    {
        $user = $this->createVerifiedUser(['is_blocked' => true]);

        $response = $this->from('/signin')->post('/signin', [
            'email' => $user->email,
            'password' => 'CorrectPass123',
        ]);

        $response->assertRedirect('/signin');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_login_with_unverified_email_returns_error(): void
    {
        $user = $this->createVerifiedUser(['email_verified_at' => null]);

        $response = $this->from('/signin')->post('/signin', [
            'email' => $user->email,
            'password' => 'CorrectPass123',
        ]);

        $response->assertRedirect('/signin');
        $response->assertSessionHasErrors(['email']);
        $this->assertGuest();
    }

    public function test_login_validation_requires_email_and_password(): void
    {
        $response = $this->from('/signin')->post('/signin', []);

        $response->assertRedirect('/signin');
        $response->assertSessionHasErrors(['email', 'password']);
    }

    // ---------------------------------------------------------------
    // Logout
    // ---------------------------------------------------------------

    public function test_logout_invalidates_session_and_redirects(): void
    {
        $user = $this->createVerifiedUser();

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();
    }

    // ---------------------------------------------------------------
    // Guest middleware
    // ---------------------------------------------------------------

    public function test_authenticated_user_cannot_access_signin_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/signin');

        $response->assertRedirect('/');
    }

    public function test_authenticated_user_cannot_access_signup_page(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/signup');

        $response->assertRedirect('/');
    }

    public function test_guest_is_redirected_from_protected_routes(): void
    {
        $response = $this->get('/my-profile');
        $response->assertRedirect('/signin');

        $response = $this->get('/change-password');
        $response->assertRedirect('/signin');
    }
}
