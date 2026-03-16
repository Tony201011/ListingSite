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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class ProviderRegisterController extends Controller
{
    /**
     * Show signup form
     */
    public function showSignupForm()
    {
        Log::info('Logging is enabled!');
        Log::error('This is an error message.');
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
            'mobile' => ['required', 'regex:/^04\d{8}$/'],
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

        $mobile = $validated['mobile']; // Format: 04XXXXXXXX

        $twilioSetting = TwilioSetting::first();
        $otp = random_int(100000, 999999);
        $isDummyOtpMode = false;

        if ($twilioSetting && $this->isDummyMobile($mobile, $twilioSetting)) {
            $isDummyOtpMode = true;
            $otp = (int) ($twilioSetting->dummy_otp ?: $otp);

            Log::info('Dummy mobile OTP mode used on signup', [
                'mobile' => $mobile,
                'dummy_mobile_number' => $twilioSetting->dummy_mobile_number,
            ]);
        }

        if (! $isDummyOtpMode) {
            if (
                ! $twilioSetting ||
                empty($twilioSetting->api_sid) ||
                empty($twilioSetting->api_secret) ||
                empty($twilioSetting->account_sid) ||
                empty($twilioSetting->phone_number)
            ) {
                Log::error('Twilio configuration missing for signup OTP send.', [
                    'mobile' => $mobile,
                ]);

                return back()->withErrors([
                    'mobile' => 'SMS service is not configured properly.'
                ])->withInput();
            }

            // Convert to E.164 format for Twilio
            $twilioMobile = $this->convertToTwilioE164($mobile);

            try {
                $client = new Client(
                    $twilioSetting->api_sid,
                    $twilioSetting->api_secret,
                    $twilioSetting->account_sid
                );

                $client->messages->create(
                    $twilioMobile,
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
       // $twilioMobile = $this->convertToTwilioE164($pendingUser['mobile']);

        if ($this->isDummyMobile($this->convertToTwilioE164($pendingUser['mobile']), $twilioSetting)) {
            $otp = (int) ($twilioSetting->dummy_otp ?: $otp);

            Cache::put($pendingKey . '_otp', [
                'code' => (string) $otp,
                'expires_at' => $otpExpiresAt->timestamp,
            ], $otpExpiresAt);

            Cache::put($pendingKey, $pendingUser, now()->addMinutes(10));
            Cache::put($resendLockKey, $resendCooldownSeconds, now()->addSeconds($resendCooldownSeconds));

            Log::info('Dummy mobile OTP mode used on resend', [
                'mobile' => $pendingUser['mobile'],
                'dummy_mobile_number' => $twilioSetting->dummy_mobile_number,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'OTP resent successfully.',
                'timer' => $otpExpirySeconds,
                'resend_cooldown' => $resendCooldownSeconds
            ]);
        }

        // Convert to E.164 format for Twilio
        $twilioMobile = $this->convertToTwilioE164($pendingUser['mobile']);

        try {
            $client = new Client(
                $twilioSetting->api_sid,
                $twilioSetting->api_secret,
                $twilioSetting->account_sid
            );

            $client->messages->create(
                $twilioMobile,
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
            ->latest('updated_at')
            ->first();

        if (! $activeMailSetting) {
            // Fallback to latest saved row to match Test Mail behavior in admin.
            $activeMailSetting = SmtpSetting::query()
                ->latest('updated_at')
                ->first();
        }

        if (! $activeMailSetting) {
            Log::error('Account created email failed: no active mail setting found.', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
            return;
        }

        if (! $activeMailSetting->is_enabled) {
            Log::warning('Account created email using latest mail setting that is disabled.', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mail_setting_id' => $activeMailSetting->id,
            ]);
        }

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
            'mail.mailers.mailgun.transport' => 'mailgun',
            'services.mailgun.domain' => $mailgunDomain,
            'services.mailgun.secret' => $activeMailSetting->mailgun_secret,
            'services.mailgun.endpoint' => $mailgunEndpoint ?: 'api.mailgun.net',
            'services.mailgun.scheme' => 'https',
            'mail.from.address' => $activeMailSetting->mail_from_address ?: 'postmaster@' . $mailgunDomain,
            'mail.from.name' => $activeMailSetting->mail_from_name ?: config('app.name'),
        ]);

        app('mail.manager')->forgetMailers();

        Log::info('Account created email attempt', [
            'user_id' => $user->id,
            'email' => $user->email,
            'mail_setting_id' => $activeMailSetting->id,
            'mail_setting_enabled' => (bool) $activeMailSetting->is_enabled,
            'mailer_used' => 'mailgun',
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            'mailgun_domain' => config('services.mailgun.domain'),
            'mailgun_endpoint' => config('services.mailgun.endpoint'),
            'mailgun_secret_present' => filled(config('services.mailgun.secret')),
        ]);

        try {
            Mail::mailer('mailgun')->send(
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
                'mailer_used' => 'mailgun',
            ]);
        } catch (\Throwable $e) {
            Log::error('Account created email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mailer_used' => 'mailgun',
                'mailgun_domain' => config('services.mailgun.domain'),
                'mailgun_endpoint' => config('services.mailgun.endpoint'),
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    private function isDummyMobile(?string $mobile, ?TwilioSetting $twilioSetting): bool
    {
        if (! $twilioSetting || ! $twilioSetting->dummy_mode_enabled) {
            return false;
        }

        if (blank($twilioSetting->dummy_mobile_number) || blank($twilioSetting->dummy_otp)) {
            return false;
        }

        return trim((string) $mobile) === trim((string) $twilioSetting->dummy_mobile_number);
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

    /**
     * Convert Australian mobile from 04XXXXXXXX to +614XXXXXXXX
     */
    private function convertToTwilioE164(string $mobile): string
    {
        return preg_replace('/^0/', '+61', $mobile);
    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/signin');
    }

    public function changePassword(Request $request)
    {
         return view('change-password');
    }

    public function deleteAccount(Request $request)
    {
         return view('delete-account');
    }

    public function shortUrl(Request $request)
    {
         return view('short-url');
    }

    public function onlineNow(Request $request)
    {
         return view('online-now');
    }

    public function availableNow(Request $request)
    {
         return view('available-now');
    }

    public function setForget(Request $request){

         return view('set-forget');

    }

    public function myBabeRank(Request $request){

         return view('my-babe-rank');

     }

     public function profileMessage(Request $request){

         return view('profile-message');

     }

     public function hideProfile(Request $request){

         return view('hide-profile');

     }

     public function clickHereToVerify(Request $request){

         return view('click-here-to-verify');

     }

     public function viewProfileSetting(Request $request){

         return view('view-profile-setting');

     }

     public function afterImageUpload(Request $request){

         return view('after-image-upload');

     }

     public function babeRank(Request $request){

         return view('babe-rank-read-more');

     }
}
