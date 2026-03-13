<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\GoogleRecaptchaSetting;
use App\Models\SiteSetting;
use App\Models\SmtpSetting;
use App\Models\TwilioSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache; // <-- Import Cache
use Illuminate\Support\Facades\Schema;

class ProviderRegisterController extends Controller
{
    /**
     * Show signup form
     */
    public function showSignupForm()
    {
        $recaptchaSetting = $this->getActiveRecaptchaSetting();
        $shouldUseRecaptcha = $this->shouldUseRecaptcha($recaptchaSetting);

        return view('signup', compact('recaptchaSetting', 'shouldUseRecaptcha'));
    }

    /**
     * Handle signup form submission
     */
    public function signup(Request $request)
    {
        $recaptchaConfig = $this->getActiveRecaptchaSetting();
        $shouldUseRecaptcha = $this->shouldUseRecaptcha($recaptchaConfig);

        $rules = [
            'email' => 'required|email|unique:users,email',
            'nickname' => 'required|string|min:3|max:255',
            'password' => 'required|min:8|confirmed',
            'mobile' => ['required', 'regex:/^\+61\d{9}$/', 'unique:users,mobile'],
            'suburb' => 'required|string|max:255',
            'age_confirm' => 'accepted',
            'referral_code' => 'nullable|string|max:255',
        ];

        if ($shouldUseRecaptcha) {
            $rules['g-recaptcha-response'] = 'required';
        }

        $validated = $request->validate($rules);

        // reCAPTCHA verification
        if ($shouldUseRecaptcha) {
            $recaptcha = $request->input('g-recaptcha-response');
            $recaptchaSecret = $recaptchaConfig?->secret_key;
            $recaptchaResponse = null;

            if ($recaptcha && $recaptchaSecret) {
                $recaptchaResponse = json_decode(
                    file_get_contents(
                        'https://www.google.com/recaptcha/api/siteverify?secret=' .
                            $recaptchaSecret .
                            '&response=' .
                            $recaptcha
                    ),
                    true
                );
            }

            if (!$recaptchaResponse || empty($recaptchaResponse['success'])) {
                return back()->withErrors([
                    'g-recaptcha-response' => 'Google reCAPTCHA verification failed. Please try again.'
                ])->withInput();
            }
        }

        $mobile = $validated['mobile'];

        if (!preg_match('/^\+61\d{9}$/', $mobile)) {
            return back()->withErrors([
                'mobile' => 'Only Australian mobile numbers in the format +614XXXXXXXX are allowed.'
            ])->withInput();
        }

        $otp = random_int(100000, 999999);

        $twilioSetting = TwilioSetting::first();

        try {
            $client = new Client(
                $twilioSetting->api_sid,
                $twilioSetting->api_secret,
                $twilioSetting->account_sid
            );

            $client->messages->create(
                $mobile,
                [
                    'from' => $twilioSetting->phone_number,
                    'body' => "Your HOTESCORT verification code is: {$otp}"
                ]
            );

            Log::info('Twilio SMS send attempt', [
                'mobile' => $mobile,
            ]);
        } catch (\Exception $e) {
            Log::error('Twilio SMS error: ' . $e->getMessage(), [
                'mobile' => $mobile,
                'referral_code' => $validated['referral_code'] ?? null,
            ]);

            return back()->withErrors([
                'mobile' => 'Failed to send OTP. Please try again.'
            ])->withInput();
        }

        // Use a temporary cache key instead of user ID
        $pendingKey = 'provider_signup_' . md5($validated['email'] . '|' . $validated['mobile']);

        // Store signup data temporarily
        Cache::put($pendingKey, [
            'name' => $validated['nickname'],
            'email' => $validated['email'],
            'mobile' => $validated['mobile'],
            'password' => Hash::make($validated['password']),
            'suburb' => $validated['suburb'],
            'role' => User::ROLE_PROVIDER,
            'mobile_verified' => false,
            'referral_code' => $validated['referral_code'] ?? null,
        ], now()->addMinutes(10));

        // Store OTP and its expiration timestamp
        $otpExpiresAt = now()->addMinutes(2);
        Cache::put($pendingKey . '_otp', [
            'code' => $otp,
            'expires_at' => $otpExpiresAt->timestamp
        ], $otpExpiresAt);

        // Store session flags
        Session::put('otp_required', true);
        Session::put('pending_signup_key', $pendingKey);

        return redirect('/otp-verification')
            ->with('success', 'OTP sent successfully. Please verify your mobile number.');
    }

    public function showSigninForm()
    {
        $recaptchaSetting = $this->getActiveRecaptchaSetting();
        $shouldUseRecaptcha = $this->shouldUseRecaptcha($recaptchaSetting);

        return view('signin', compact('recaptchaSetting', 'shouldUseRecaptcha'));
    }

    public function signin(Request $request)
    {
        $recaptchaConfig = $this->getActiveRecaptchaSetting();
        $shouldUseRecaptcha = $this->shouldUseRecaptcha($recaptchaConfig);

        $rules = [
            'email' => 'required|email',
            'password' => 'required',
        ];

        if ($shouldUseRecaptcha) {
            $rules['g-recaptcha-response'] = 'required';
        }

        $request->validate($rules);

        if ($shouldUseRecaptcha) {
            $recaptcha = $request->input('g-recaptcha-response');
            $recaptchaSecret = $recaptchaConfig?->secret_key;
            $recaptchaResponse = null;

            if ($recaptcha && $recaptchaSecret) {
                $recaptchaResponse = json_decode(
                    file_get_contents(
                        'https://www.google.com/recaptcha/api/siteverify?secret=' .
                            $recaptchaSecret .
                            '&response=' .
                            $recaptcha
                    ),
                    true
                );
            }

            if (!$recaptchaResponse || empty($recaptchaResponse['success'])) {
                return back()->withErrors(['g-recaptcha-response' => 'reCAPTCHA verification failed'])->withInput();
            }
        }

        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid email or password'
        ])->withInput();
    }

    public function otpVerficationForm()
    {
        if (!session()->has('otp_required') || !session()->has('pending_signup_key')) {
            return redirect('/signup')->withErrors([
                'session' => 'OTP session expired. Please signup again.'
            ]);
        }

        $pendingKey = session()->get('pending_signup_key');
        $pendingUser = Cache::get($pendingKey);

        if (!$pendingUser) {
            return redirect('/signup')->withErrors([
                'session' => 'Signup session expired. Please signup again.'
            ]);
        }

        $otpData = Cache::get($pendingKey . '_otp');
        $remainingTime = 0;
        if ($otpData && isset($otpData['expires_at'])) {
            $remainingTime = $otpData['expires_at'] - time();
            if ($remainingTime < 0) {
                $remainingTime = 0;
            }
        }
        return view('otp-verification', [
            'userData' => (object) $pendingUser,
            'remainingTime' => $remainingTime
        ]);
    }


    public function resendOtp(Request $request)
{
    if (!Session::has('otp_required') || !Session::has('pending_signup_key')) {
        return response()->json([
            'success' => false,
            'message' => 'OTP session expired. Please signup again.'
        ], 422);
    }

    $pendingKey = Session::get('pending_signup_key');
    $pendingUser = Cache::get($pendingKey);

    if (!$pendingUser || empty($pendingUser['mobile'])) {
        return response()->json([
            'success' => false,
            'message' => 'Signup session expired. Please signup again.'
        ], 422);
    }

    // Prevent OTP resend spam
    $resendLockKey = $pendingKey . '_resend_lock';
    if (Cache::has($resendLockKey)) {
        $remainingCooldown = (int) Cache::get($resendLockKey, 0);

        return response()->json([
            'success' => false,
            'message' => $remainingCooldown > 0
                ? "Please wait {$remainingCooldown} seconds before requesting another OTP."
                : 'Please wait before requesting another OTP.'
        ], 429);
    }

    $twilioSetting = TwilioSetting::first();

    if (
        !$twilioSetting ||
        empty($twilioSetting->api_sid) ||
        empty($twilioSetting->api_secret) ||
        empty($twilioSetting->account_sid) ||
        empty($twilioSetting->phone_number)
    ) {
        Log::error('Twilio configuration missing for OTP resend.');

        return response()->json([
            'success' => false,
            'message' => 'SMS service is not configured properly.'
        ], 500);
    }

    $otp = random_int(100000, 999999);
    $otpExpirySeconds = 120;
    $resendCooldownSeconds = 30;
    $otpExpiresAt = now()->addSeconds($otpExpirySeconds);

    try {
        $client = new Client(
            $twilioSetting->api_sid,
            $twilioSetting->api_secret,
            $twilioSetting->account_sid
        );

        $client->messages->create(
            $pendingUser['mobile'],
            [
                'from' => $twilioSetting->phone_number,
                'body' => "Your HOTESCORT verification code is: {$otp}"
            ]
        );

        // Save OTP only after SMS send succeeds
        Cache::put($pendingKey . '_otp', [
            'code' => (string) $otp,
            'expires_at' => $otpExpiresAt->timestamp,
        ], $otpExpiresAt);

        // Refresh pending signup cache lifetime
        Cache::put($pendingKey, $pendingUser, now()->addMinutes(10));

        // Lock resend for 30 seconds
        Cache::put($resendLockKey, $resendCooldownSeconds, now()->addSeconds($resendCooldownSeconds));

        Log::info('Twilio SMS resend success', [
            'mobile' => $pendingUser['mobile'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'OTP resent successfully.',
            'timer' => $otpExpirySeconds,
            'resend_cooldown' => $resendCooldownSeconds
        ]);
    } catch (\Exception $e) {
        Log::error('Twilio SMS resend error', [
            'mobile' => $pendingUser['mobile'],
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'success' => false,
            'message' => 'Failed to resend OTP.'
        ], 500);
    }
}

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|digits:6'
        ]);

        if (!session()->has('otp_required') || !session()->has('pending_signup_key')) {
            return response()->json([
                'success' => false,
                'message' => 'OTP session expired. Please signup again.'
            ], 422);
        }

        $pendingKey = session()->get('pending_signup_key');
        $pendingUser = Cache::get($pendingKey);
        $otpData = Cache::get($pendingKey . '_otp');
        if (!$pendingUser || !$otpData || !isset($otpData['code'], $otpData['expires_at'])) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired. Please signup again.'
            ], 422);
        }
        if (time() > $otpData['expires_at']) {
            return response()->json([
                'success' => false,
                'message' => 'OTP expired. Please signup again.'
            ], 422);
        }
        if ((string) $request->otp !== (string) $otpData['code']) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid OTP.'
            ], 422);
        }

        if (User::where('email', $pendingUser['email'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Email already exists.'
            ], 422);
        }

        if (User::where('mobile', $pendingUser['mobile'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'Mobile number already exists.'
            ], 422);
        }

        $user = User::create([
            'name' => $pendingUser['name'],
            'email' => $pendingUser['email'],
            'mobile' => $pendingUser['mobile'],
            'password' => $pendingUser['password'],
            'suburb' => $pendingUser['suburb'],
            'role' => $pendingUser['role'],
            'mobile_verified' => true,
            'referral_code' => $pendingUser['referral_code'],
        ]);

        $this->sendAccountCreatedEmail($user);

        Cache::forget($pendingKey);
        Cache::forget($pendingKey . '_otp');

        Session::forget('otp_required');
        Session::forget('pending_signup_key');

        Auth::login($user);

        return response()->json([
            'success' => true,
            'message' => 'Account created successfully.',
            'redirect' => url('/signin')
        ]);
    }

    private function sendAccountCreatedEmail(User $user): void
    {
        $activeMailSetting = SmtpSetting::query()
            ->where('is_enabled', true)
            ->first();

        if ($activeMailSetting) {
            $sandboxDomain = $activeMailSetting->mailgun_sandbox_domain ?: $activeMailSetting->mailgun_domain;
            $liveDomain = $activeMailSetting->mailgun_live_domain;
            $mailgunDomain = $activeMailSetting->use_mailgun_sandbox
                ? $sandboxDomain
                : ($liveDomain ?: $sandboxDomain);

            $mailgunEndpoint = $activeMailSetting->mailgun_endpoint ?: 'api.mailgun.net';

            if (filled($mailgunDomain)) {
                $mailgunDomain = preg_replace('#^https?://#i', '', rtrim(trim($mailgunDomain), '/'));
            }

            if (filled($mailgunEndpoint)) {
                $mailgunEndpoint = parse_url(trim($mailgunEndpoint), PHP_URL_HOST)
                    ?: preg_replace('#^https?://#i', '', rtrim(trim($mailgunEndpoint), '/'));
            }

            config([
                'mail.default' => $activeMailSetting->mail_mailer ?: 'mailgun',
                'services.mailgun.domain' => $mailgunDomain,
                'services.mailgun.secret' => $activeMailSetting->mailgun_secret,
                'services.mailgun.endpoint' => $mailgunEndpoint ?: 'api.mailgun.net',
                'mail.from.address' => $activeMailSetting->mail_from_address ?: config('mail.from.address'),
                'mail.from.name' => $activeMailSetting->mail_from_name ?: config('mail.from.name'),
            ]);

            app('mail.manager')->forgetMailers();
        }

        $defaultMailer = (string) config('mail.default', 'log');
        $hasMailgunCredentials = filled(config('services.mailgun.domain'))
            && filled(config('services.mailgun.secret'));

        // If default mailer is log/array but Mailgun creds exist, use mailgun for actual delivery.
        $mailerToUse = in_array($defaultMailer, ['log', 'array'], true) && $hasMailgunCredentials
            ? 'mailgun'
            : $defaultMailer;

        Log::info('Account created email attempt', [
            'user_id' => $user->id,
            'email' => $user->email,
            'db_mail_setting_found' => (bool) $activeMailSetting,
            'mailer_default' => $defaultMailer,
            'mailer_used' => $mailerToUse,
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            'mailgun_domain' => config('services.mailgun.domain'),
            'mailgun_endpoint' => config('services.mailgun.endpoint'),
            'mailgun_secret_present' => filled(config('services.mailgun.secret')),
            'app_env' => app()->environment(),
        ]);

        try {
            $sentMessage = Mail::mailer($mailerToUse)->send(
                'emails.account-created',
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'signinUrl' => url('/signin'),
                ],
                function ($message) use ($user): void {
                    $message->to($user->email)
                        ->subject('Your account has been created');
                }
            );

            Log::info('Account created email sent successfully', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mailer_used' => $mailerToUse,
                'message_id' => is_object($sentMessage) && method_exists($sentMessage, 'getMessageId') ? $sentMessage->getMessageId() : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Account created email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mailer_default' => $defaultMailer,
                'mailer_used' => $mailerToUse,
                'mailgun_domain' => config('services.mailgun.domain'),
                'mailgun_endpoint' => config('services.mailgun.endpoint'),
                'mailgun_secret_present' => filled(config('services.mailgun.secret')),
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function getActiveRecaptchaSetting(): ?GoogleRecaptchaSetting
    {
        return GoogleRecaptchaSetting::where('is_active', 1)->first();
    }

    private function shouldUseRecaptcha(?GoogleRecaptchaSetting $recaptchaSetting): bool
    {
        if (! $this->isCaptchaEnabledInSiteSettings()) {
            return false;
        }

        return filled($recaptchaSetting?->site_key)
            && filled($recaptchaSetting?->secret_key);
    }

    private function isCaptchaEnabledInSiteSettings(): bool
    {
        if (! Schema::hasTable('site_settings')) {
            return true;
        }

        $siteSetting = SiteSetting::query()->latest('updated_at')->first();

        return $siteSetting?->captcha_enabled ?? true;
    }


}