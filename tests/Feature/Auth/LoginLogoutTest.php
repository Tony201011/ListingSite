<?php

namespace Tests\Feature\Auth;

use App\Models\OnlineUser;
use App\Models\ProviderOnlineLog;
use App\Models\ProviderProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LoginLogoutTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

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

        $response->assertRedirect('/my-profiles');
        $response->assertSessionHas('auth_session_sync', fn (array $payload): bool => ($payload['type'] ?? null) === 'login' && filled($payload['id'] ?? null));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_allows_user_with_blocked_profile(): void
    {
        $user = $this->createVerifiedUser();

        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Blocked Listing',
            'slug' => 'blocked-listing-'.$user->id,
            'profile_status' => 'approved',
            'is_blocked' => true,
        ]);

        $response = $this->from('/signin')->post('/signin', [
            'email' => $user->email,
            'password' => 'CorrectPass123',
        ]);

        $response->assertRedirect('/my-profiles');
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

    public function test_admin_login_uses_admin_guard_only(): void
    {
        $admin = $this->createVerifiedUser([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this->from('/signin')->post('/signin', [
            'email' => $admin->email,
            'password' => 'CorrectPass123',
        ]);

        $response->assertRedirect('/admin');
        $response->assertSessionHas('auth_session_sync', fn (array $payload): bool => ($payload['type'] ?? null) === 'login' && filled($payload['id'] ?? null));
        $this->assertAuthenticated('admin');
        $this->assertGuest('web');
    }

    public function test_admin_login_ignores_frontend_intended_url_and_redirects_to_admin_panel(): void
    {
        $admin = $this->createVerifiedUser([
            'role' => User::ROLE_ADMIN,
        ]);

        $response = $this
            ->withSession(['url.intended' => '/my-profile'])
            ->from('/signin')
            ->post('/signin', [
                'email' => $admin->email,
                'password' => 'CorrectPass123',
            ]);

        $response->assertRedirect('/admin');
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

    public function test_login_with_unverified_email_redirects_to_verification_notice(): void
    {
        $user = $this->createVerifiedUser(['email_verified_at' => null]);

        $response = $this->from('/signin')->post('/signin', [
            'email' => $user->email,
            'password' => 'CorrectPass123',
        ]);

        $response->assertRedirect(route('verification.notice'));
        $this->assertAuthenticatedAs($user);
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

    public function test_logout_clears_admin_guard_in_same_session(): void
    {
        $provider = $this->createVerifiedUser();
        $admin = $this->createVerifiedUser([
            'role' => User::ROLE_ADMIN,
        ]);

        Auth::guard('admin')->login($admin, true);

        $response = $this
            ->withCookie(Auth::guard('admin')->getRecallerName(), 'remember-token')
            ->actingAs($provider, 'web')
            ->post('/logout');

        $response->assertRedirect('/');
        $response->assertCookieExpired(Auth::guard('admin')->getRecallerName());
        $this->assertGuest('web');
        $this->assertGuest('admin');
    }

    public function test_logout_closes_open_online_sessions_and_calculates_duration(): void
    {
        Carbon::setTestNow('2026-05-22 11:30:00');

        $user = $this->createVerifiedUser([
            'role' => User::ROLE_PROVIDER,
        ]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Selected Profile',
            'slug' => 'selected-profile-'.$user->id,
        ]);

        $startedAt = now()->copy()->subHour()->subMinutes(45);

        OnlineUser::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'status' => 'online',
            'usage_date' => today(),
            'usage_count' => 1,
            'online_started_at' => $startedAt,
            'online_expires_at' => null,
        ]);

        $sessionLog = ProviderOnlineLog::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'went_online_at' => $startedAt,
            'went_offline_at' => null,
            'duration_seconds' => null,
        ]);

        $response = $this->actingAs($user)->post('/logout');

        $response->assertRedirect('/');
        $this->assertGuest();

        $onlineUser = OnlineUser::query()->where('user_id', $user->id)->first();
        $this->assertNotNull($onlineUser);
        $this->assertSame('offline', $onlineUser->status);
        $this->assertNull($onlineUser->online_started_at);
        $this->assertNull($onlineUser->online_expires_at);

        $sessionLog->refresh();
        $this->assertNotNull($sessionLog->went_offline_at);
        $this->assertSame(6300, $sessionLog->duration_seconds);
        $this->assertTrue($sessionLog->went_offline_at->equalTo(now()));
    }

    public function test_logged_out_session_cannot_access_json_provider_routes(): void
    {
        $user = $this->createVerifiedUser();

        $this->actingAs($user)->post('/logout');

        $response = $this->postJson('/online-status', ['status' => 'online']);

        $response->assertStatus(401)
            ->assertJson(['message' => 'Unauthenticated.']);
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

    public function test_signin_page_renders_login_sync_trigger_from_session_flash(): void
    {
        $response = $this->withSession([
            'auth_session_sync' => [
                'id' => 'login-event',
                'type' => 'login',
                'timestamp' => now()->getTimestampMs(),
            ],
        ])->get('/signin');

        $response->assertOk()
            ->assertSee('data-authenticated="0"', false)
            ->assertSee('window.authSessionSync?.notifyLogin({"id":"login-event","type":"login"', false);
    }

    public function test_authenticated_frontend_pages_mark_body_as_authenticated_for_session_sync(): void
    {
        $user = $this->createVerifiedUser(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Profile A',
            'slug' => 'profile-a-'.$user->id,
        ]);
        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Profile B',
            'slug' => 'profile-b-'.$user->id,
        ]);

        $response = $this->actingAs($user)->get('/my-profiles');

        $response->assertOk()
            ->assertSee('data-authenticated="1"', false)
            ->assertSee('data-auth-protected="1"', false);
    }

    public function test_guest_is_redirected_from_protected_routes(): void
    {
        $response = $this->get('/my-profile');
        $response->assertRedirect('/signin');

        $response = $this->get('/change-password');
        $response->assertRedirect('/signin');
    }

    // ---------------------------------------------------------------
    // Blocked user access
    // ---------------------------------------------------------------

    public function test_blocked_user_is_logged_out_when_accessing_provider_route(): void
    {
        $user = $this->createVerifiedUser(['is_blocked' => true]);

        $response = $this->actingAs($user)->get('/my-profiles');

        $response->assertRedirect('/signin');
        $this->assertGuest();
    }

    public function test_blocked_user_accessing_json_provider_route_gets_403(): void
    {
        $user = $this->createVerifiedUser(['is_blocked' => true]);

        $response = $this->actingAs($user)
            ->postJson('/online-status', ['status' => 'online']);

        $response->assertStatus(403)
            ->assertJson(['message' => 'Your account has been blocked.']);
        $this->assertGuest();
    }
}
