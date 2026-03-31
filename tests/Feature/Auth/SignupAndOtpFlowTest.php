<?php

namespace Tests\Feature\Auth;

use App\Models\TwilioSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;
use Tests\TestCase;

class SignupAndOtpFlowTest extends TestCase
{
    use RefreshDatabase;

    private const DUMMY_MOBILE = '0400000000';
    private const DUMMY_OTP = '123456';

    protected function setUp(): void
    {
        parent::setUp();

        TwilioSetting::create([
            'account_sid' => 'test_sid',
            'api_sid' => 'test_api_sid',
            'api_secret' => 'test_api_secret',
            'phone_number' => '+1234567890',
            'dummy_mode_enabled' => true,
            'dummy_mobile_number' => self::DUMMY_MOBILE,
            'dummy_otp' => self::DUMMY_OTP,
        ]);
    }

    private function validSignupPayload(array $overrides = []): array
    {
        return array_merge([
            'email' => 'newprovider@example.com',
            'nickname' => 'providerNick',
            'password' => 'SecurePass123',
            'password_confirmation' => 'SecurePass123',
            'mobile' => self::DUMMY_MOBILE,
            'suburb' => 'Sydney',
            'age_confirm' => '1',
        ], $overrides);
    }

    // ---------------------------------------------------------------
    // Signup validation
    // ---------------------------------------------------------------

    public function test_signup_requires_all_mandatory_fields(): void
    {
        $response = $this->from('/signup')->post('/signup', []);

        $response->assertRedirect('/signup');
        $response->assertSessionHasErrors(['email', 'nickname', 'password', 'mobile', 'suburb', 'age_confirm']);
    }

    public function test_signup_rejects_invalid_mobile_format(): void
    {
        $response = $this->from('/signup')->post('/signup', $this->validSignupPayload([
            'mobile' => '1234567890',
        ]));

        $response->assertRedirect('/signup');
        $response->assertSessionHasErrors(['mobile']);
    }

    public function test_signup_rejects_duplicate_email(): void
    {
        User::factory()->create(['email' => 'taken@example.com']);

        $response = $this->from('/signup')->post('/signup', $this->validSignupPayload([
            'email' => 'taken@example.com',
        ]));

        $response->assertRedirect('/signup');
        $response->assertSessionHasErrors(['email']);
    }

    public function test_signup_rejects_password_shorter_than_8_chars(): void
    {
        $response = $this->from('/signup')->post('/signup', $this->validSignupPayload([
            'password' => 'short',
            'password_confirmation' => 'short',
        ]));

        $response->assertRedirect('/signup');
        $response->assertSessionHasErrors(['password']);
    }

    public function test_signup_rejects_mismatched_password_confirmation(): void
    {
        $response = $this->from('/signup')->post('/signup', $this->validSignupPayload([
            'password' => 'SecurePass123',
            'password_confirmation' => 'DifferentPass',
        ]));

        $response->assertRedirect('/signup');
        $response->assertSessionHasErrors(['password']);
    }

    // ---------------------------------------------------------------
    // Successful signup redirects to OTP page
    // ---------------------------------------------------------------

    public function test_successful_signup_redirects_to_otp_verification(): void
    {
        $response = $this->from('/signup')->post('/signup', $this->validSignupPayload());

        $response->assertRedirect('/otp-verification');
        $response->assertSessionHas('success', 'OTP sent successfully. Please verify your mobile number.');
    }

    public function test_signup_stores_pending_data_in_cache(): void
    {
        $this->from('/signup')->post('/signup', $this->validSignupPayload());

        $pendingKey = session('pending_signup_key');
        $this->assertNotNull($pendingKey);

        $pendingUser = Cache::get($pendingKey);
        $this->assertNotNull($pendingUser);
        $this->assertSame('newprovider@example.com', $pendingUser['email']);
        $this->assertSame('providerNick', $pendingUser['name']);
        $this->assertSame(self::DUMMY_MOBILE, $pendingUser['mobile']);
    }

    public function test_signup_stores_hashed_otp_in_cache(): void
    {
        $this->from('/signup')->post('/signup', $this->validSignupPayload());

        $pendingKey = session('pending_signup_key');
        $otpData = Cache::get($pendingKey . '_otp');

        $this->assertNotNull($otpData);
        $this->assertArrayHasKey('code', $otpData);
        $this->assertArrayHasKey('expires_at', $otpData);
        $this->assertTrue(Hash::check(self::DUMMY_OTP, $otpData['code']));
    }

    // ---------------------------------------------------------------
    // OTP verification success
    // ---------------------------------------------------------------

    public function test_otp_verify_success_creates_user_and_returns_redirect(): void
    {
        // Signup first
        $this->from('/signup')->post('/signup', $this->validSignupPayload());

        // Verify OTP
        $response = $this->postJson('/verify-otp', ['otp' => self::DUMMY_OTP]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'Account created successfully.',
        ]);
        $response->assertJsonStructure(['redirect']);

        // User was created
        $this->assertDatabaseHas('users', [
            'email' => 'newprovider@example.com',
            'name' => 'providerNick',
            'mobile' => self::DUMMY_MOBILE,
            'mobile_verified' => true,
            'role' => User::ROLE_PROVIDER,
        ]);
    }

    public function test_otp_verify_success_logs_in_the_user(): void
    {
        $this->from('/signup')->post('/signup', $this->validSignupPayload());

        $this->postJson('/verify-otp', ['otp' => self::DUMMY_OTP]);

        $this->assertAuthenticated();
    }

    public function test_otp_verify_success_clears_pending_session(): void
    {
        $this->from('/signup')->post('/signup', $this->validSignupPayload());

        $pendingKey = session('pending_signup_key');

        $this->postJson('/verify-otp', ['otp' => self::DUMMY_OTP]);

        $this->assertNull(Cache::get($pendingKey));
        $this->assertNull(Cache::get($pendingKey . '_otp'));
        $this->assertFalse(session()->has('otp_required'));
        $this->assertFalse(session()->has('pending_signup_key'));
    }

    // ---------------------------------------------------------------
    // OTP verification failure
    // ---------------------------------------------------------------

    public function test_otp_verify_with_wrong_code_returns_error(): void
    {
        $this->from('/signup')->post('/signup', $this->validSignupPayload());

        $response = $this->postJson('/verify-otp', ['otp' => '999999']);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $response->assertJsonFragment(['message' => 'Invalid OTP. 4 attempt(s) remaining.']);

        $this->assertDatabaseMissing('users', ['email' => 'newprovider@example.com']);
    }

    public function test_otp_verify_decrements_remaining_attempts(): void
    {
        $this->from('/signup')->post('/signup', $this->validSignupPayload());

        // First wrong attempt → 4 remaining
        $response = $this->postJson('/verify-otp', ['otp' => '111111']);
        $response->assertJsonFragment(['message' => 'Invalid OTP. 4 attempt(s) remaining.']);

        // Second wrong attempt → 3 remaining
        $response = $this->postJson('/verify-otp', ['otp' => '222222']);
        $response->assertJsonFragment(['message' => 'Invalid OTP. 3 attempt(s) remaining.']);
    }

    public function test_otp_verify_with_invalid_format_fails_validation(): void
    {
        $this->from('/signup')->post('/signup', $this->validSignupPayload());

        // Not 6 digits
        $response = $this->postJson('/verify-otp', ['otp' => '12345']);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['otp']);

        // Non-numeric
        $response = $this->postJson('/verify-otp', ['otp' => 'abcdef']);
        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['otp']);
    }

    // ---------------------------------------------------------------
    // OTP lockout after too many attempts
    // ---------------------------------------------------------------

    public function test_otp_lockout_after_five_failed_attempts(): void
    {
        $this->from('/signup')->post('/signup', $this->validSignupPayload());

        // Use up all 5 attempts
        for ($i = 0; $i < 4; $i++) {
            $this->postJson('/verify-otp', ['otp' => sprintf('%06d', $i)]);
        }

        // 5th attempt triggers lockout
        $response = $this->postJson('/verify-otp', ['otp' => '000005']);
        $response->assertStatus(429);
        $response->assertJson([
            'success' => false,
            'message' => 'Too many failed attempts. Please signup again.',
        ]);

        // No user was created
        $this->assertDatabaseMissing('users', ['email' => 'newprovider@example.com']);
    }

    // ---------------------------------------------------------------
    // OTP verification with expired/missing session
    // ---------------------------------------------------------------

    public function test_otp_verify_without_session_returns_error(): void
    {
        $response = $this->postJson('/verify-otp', ['otp' => '123456']);

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'OTP session expired. Please signup again.',
        ]);
    }

    // ---------------------------------------------------------------
    // OTP resend
    // ---------------------------------------------------------------

    public function test_otp_resend_returns_success(): void
    {
        $this->from('/signup')->post('/signup', $this->validSignupPayload());

        $response = $this->postJson('/resend-otp');

        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'message' => 'OTP resent successfully.',
        ]);
        $response->assertJsonStructure(['timer', 'resend_cooldown']);
    }

    public function test_otp_resend_without_session_returns_error(): void
    {
        $response = $this->postJson('/resend-otp');

        $response->assertStatus(422);
        $response->assertJson([
            'success' => false,
            'message' => 'OTP session expired. Please signup again.',
        ]);
    }

    public function test_otp_resend_respects_cooldown(): void
    {
        $this->from('/signup')->post('/signup', $this->validSignupPayload());

        // First resend should succeed
        $response = $this->postJson('/resend-otp');
        $response->assertOk();

        // Immediate second resend should be blocked by cooldown
        $response = $this->postJson('/resend-otp');
        $response->assertStatus(429);
        $response->assertJson(['success' => false]);
    }

    public function test_otp_resend_blocks_after_max_resends(): void
    {
        $this->from('/signup')->post('/signup', $this->validSignupPayload());

        $pendingKey = session('pending_signup_key');

        // Manually set resend count to max (5)
        Cache::put($pendingKey . '_resend_count', 5, now()->addMinutes(15));

        $response = $this->postJson('/resend-otp');

        $response->assertStatus(429);
        $response->assertJson([
            'success' => false,
            'message' => 'Too many OTP resend requests. Please signup again.',
        ]);
    }

    public function test_otp_resend_resets_failed_attempts(): void
    {
        $this->from('/signup')->post('/signup', $this->validSignupPayload());

        // Fail 3 times
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/verify-otp', ['otp' => sprintf('%06d', $i)]);
        }

        // Wait out cooldown conceptually — just clear the lock
        $pendingKey = session('pending_signup_key');
        Cache::forget($pendingKey . '_resend_lock');

        // Resend OTP
        $this->postJson('/resend-otp');

        // Now verify with correct OTP — should succeed (attempts reset)
        $response = $this->postJson('/verify-otp', ['otp' => self::DUMMY_OTP]);
        $response->assertOk();
        $response->assertJson(['success' => true]);
    }

    // ---------------------------------------------------------------
    // OTP verification page
    // ---------------------------------------------------------------

    public function test_otp_page_shows_verification_form_with_active_session(): void
    {
        $this->from('/signup')->post('/signup', $this->validSignupPayload());

        $response = $this->get('/otp-verification');

        $response->assertOk();
        $response->assertViewIs('auth.otp-verification');
    }

    public function test_otp_page_redirects_to_signup_without_session(): void
    {
        $response = $this->get('/otp-verification');

        $response->assertRedirect('/signup');
    }
}
