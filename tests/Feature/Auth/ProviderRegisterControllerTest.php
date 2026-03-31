<?php

namespace Tests\Feature\Auth;

use App\Actions\Auth\BuildAuthPageData;
use App\Actions\Auth\ChangeProviderPassword;
use App\Actions\Auth\LogoutProvider;
use App\Actions\Auth\ResendProviderSignupOtp;
use App\Actions\Auth\ShowProviderOtpVerificationData;
use App\Actions\Auth\SigninProvider;
use App\Actions\Auth\SignupProvider;
use App\Actions\Auth\VerifyProviderSignupOtp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Mockery;
use Tests\TestCase;

class ProviderRegisterControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Mockery::close();

        parent::tearDown();
    }

    public function test_show_signup_form_returns_signup_view(): void
    {
        $buildAuthPageData = Mockery::mock(BuildAuthPageData::class);
        $buildAuthPageData->shouldReceive('execute')
            ->once()
            ->andReturn([
                'siteName' => 'Demo Site',
            ]);

        $this->app->instance(BuildAuthPageData::class, $buildAuthPageData);

        $response = $this->get(route('signup'));

        $response->assertOk();
        $response->assertViewIs('auth.signup');
        $response->assertViewHas('siteName', 'Demo Site');
    }

    public function test_signup_delegates_to_signup_provider_and_returns_redirect(): void
    {
        $validated = [
            'email' => 'provider@example.com',
            'nickname' => 'provider1',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'mobile' => '0412345678',
            'suburb' => 'Sydney',
            'age_confirm' => '1',
        ];

        $redirectResponse = redirect('/otp-verification')
            ->with('success', 'OTP sent successfully. Please verify your mobile number.');

        $signupProvider = Mockery::mock(SignupProvider::class);
        $signupProvider->shouldReceive('execute')
            ->once()
            ->with(Mockery::on(function (array $payload) {
                return $payload['email'] === 'provider@example.com'
                    && $payload['nickname'] === 'provider1'
                    && $payload['mobile'] === '0412345678'
                    && $payload['suburb'] === 'Sydney';
            }))
            ->andReturn($redirectResponse);

        $this->app->instance(SignupProvider::class, $signupProvider);

        $response = $this->from('/signup')->post(route('signup.submit'), $validated);

        $response->assertRedirect('/otp-verification');
        $response->assertSessionHas('success', 'OTP sent successfully. Please verify your mobile number.');
    }

    public function test_show_signin_form_returns_signin_view(): void
    {
        $buildAuthPageData = Mockery::mock(BuildAuthPageData::class);
        $buildAuthPageData->shouldReceive('execute')
            ->once()
            ->andReturn([
                'siteName' => 'Demo Site',
            ]);

        $this->app->instance(BuildAuthPageData::class, $buildAuthPageData);

        $response = $this->get(route('signin'));

        $response->assertOk();
        $response->assertViewIs('auth.signin');
        $response->assertViewHas('siteName', 'Demo Site');
    }

    public function test_signin_delegates_to_signin_provider_and_returns_redirect(): void
    {
        $redirectResponse = redirect('/dashboard');

        $signinProvider = Mockery::mock(SigninProvider::class);
        $signinProvider->shouldReceive('execute')
            ->once()
            ->with(Mockery::type(Request::class))
            ->andReturn($redirectResponse);

        $this->app->instance(SigninProvider::class, $signinProvider);

        $response = $this->from('/signin')->post(route('signin.submit'), [
            'email' => 'provider@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/dashboard');
    }

    public function test_otp_verification_form_returns_view_when_action_returns_data(): void
    {
        $showProviderOtpVerificationData = Mockery::mock(ShowProviderOtpVerificationData::class);
        $showProviderOtpVerificationData->shouldReceive('execute')
            ->once()
            ->andReturn([
                'masked_mobile' => '04******78',
                'expires_in' => 600,
            ]);

        $this->app->instance(
            ShowProviderOtpVerificationData::class,
            $showProviderOtpVerificationData
        );

        $response = $this->get(route('otp-verification'));

        $response->assertOk();
        $response->assertViewIs('auth.otp-verification');
        $response->assertViewHas('masked_mobile', '04******78');
        $response->assertViewHas('expires_in', 600);
    }

    public function test_otp_verification_form_redirects_when_action_returns_redirect(): void
    {
        $showProviderOtpVerificationData = Mockery::mock(ShowProviderOtpVerificationData::class);
        $showProviderOtpVerificationData->shouldReceive('execute')
            ->once()
            ->andReturn([
                'redirect' => '/signup',
                'errors' => new MessageBag([
                    'otp' => ['OTP session expired.'],
                ]),
            ]);

        $this->app->instance(
            ShowProviderOtpVerificationData::class,
            $showProviderOtpVerificationData
        );

        $response = $this->from('/otp-verification')->get(route('otp-verification'));

        $response->assertRedirect('/signup');
        $response->assertSessionHasErrors(['otp']);
    }

    public function test_resend_otp_returns_json_response_from_action(): void
    {
        $resendProviderSignupOtp = Mockery::mock(ResendProviderSignupOtp::class);
        $resendProviderSignupOtp->shouldReceive('execute')
            ->once()
            ->andReturn([
                'status' => 200,
                'data' => [
                    'message' => 'OTP resent successfully.',
                ],
            ]);

        $this->app->instance(ResendProviderSignupOtp::class, $resendProviderSignupOtp);

        $response = $this->postJson(route('resend.otp'));

        $response->assertOk();
        $response->assertJson([
            'message' => 'OTP resent successfully.',
        ]);
    }

    public function test_verify_otp_returns_json_response_from_action(): void
    {
        $verifyProviderSignupOtp = Mockery::mock(VerifyProviderSignupOtp::class);
        $verifyProviderSignupOtp->shouldReceive('execute')
            ->once()
            ->with('123456')
            ->andReturn([
                'status' => 200,
                'data' => [
                    'message' => 'OTP verified successfully.',
                ],
            ]);

        $this->app->instance(VerifyProviderSignupOtp::class, $verifyProviderSignupOtp);

        $response = $this->postJson(route('verify.otp'), [
            'otp' => '123456',
        ]);

        $response->assertOk();
        $response->assertJson([
            'message' => 'OTP verified successfully.',
        ]);
    }

    public function test_logout_delegates_to_logout_provider(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $response->assertRedirect('/');
    }

    public function test_change_password_returns_view(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('change-password'));

        $response->assertOk();
        $response->assertViewIs('auth.change-password');
    }

    public function test_update_password_returns_json_response_from_action(): void
    {
        $user = User::factory()->create();

        $changeProviderPassword = Mockery::mock(ChangeProviderPassword::class);
        $changeProviderPassword->shouldReceive('execute')
            ->once()
            ->with(
                Mockery::on(fn ($arg) => $arg->is($user)),
                'new-secret-123'
            )
            ->andReturn([
                'message' => 'Password updated successfully.',
            ]);

        $this->app->instance(ChangeProviderPassword::class, $changeProviderPassword);

        $response = $this->actingAs($user)->postJson(route('change-password.update'), [
            'current_password' => 'password',
            'new_password' => 'new-secret-123',
            'new_password_confirmation' => 'new-secret-123',
        ]);

        $response->assertOk();
        $response->assertJson([
            'message' => 'Password updated successfully.',
        ]);
    }

    public function test_delete_account_returns_view(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('account.delete-page'));

        $response->assertOk();
        $response->assertViewIs('auth.delete-account');
    }
}
