<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\GoogleRecaptchaSetting;
use App\Models\TwilioSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter; // <-- For rate limiting

class ProviderRegisterController extends Controller
{
    /**
     * Show signup form
     */
    public function showSignupForm()
    {
        $recaptchaSetting = GoogleRecaptchaSetting::where('is_active', 1)->first();
        return view('signup', compact('recaptchaSetting'));
    }

    /**
     * Handle signup form submission
     */
    public function signup(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'nickname' => 'required|string|min:3|max:255',
            'password' => 'min:8|required_with:password_confirmation|same:password_confirmation',
            'password_confirmation' => 'min:8',
            'mobile' => ['required', 'regex:/^\+61\d{9}$/'],
            'suburb' => 'required|string|max:255',
            'age_confirm' => 'accepted',
            'g-recaptcha-response' => 'required',
            'referral_code' => 'nullable|string|max:255',
        ]);

        // reCAPTCHA verification
        $recaptchaConfig = GoogleRecaptchaSetting::where('is_active', 1)->first();
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

        $mobile = $validated['mobile'];
        $australianPattern = '/^\+61\d{9}$/';
        if (!preg_match($australianPattern, $mobile)) {
            return back()->withErrors(['mobile' => 'Only Australian mobile numbers in the format +614XXXXXXXX are allowed.'])->withInput();
        }

        // Create user first
        $user = User::create([
            'name' => $validated['nickname'],
            'email' => $validated['email'],
            'mobile' => $mobile,
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_PROVIDER,
            'mobile_verified' => false,
            'referral_code' => $validated['referral_code'] ?? null
        ]);

        // Generate and hash OTP
        $otp = rand(100000, 999999);
        $otpHash = Hash::make($otp);
        $otpKey = 'otp_signup_' . $user->id; // purpose-specific key
        Cache::put($otpKey, [
            'hash' => $otpHash,
            'attempts' => 0,
        ], now()->addSeconds(120)); // 120 seconds expiry

        // Send SMS via Twilio
        $twilioSetting = TwilioSetting::first();
        $account_sid = $twilioSetting->account_sid;
        $api_sid = $twilioSetting->api_sid;
        $api_secret = $twilioSetting->api_secret;

        try {
            $client = new Client($api_sid, $api_secret, $account_sid);
            $client->messages->create(
                $mobile,
                [
                    'from' => $twilioSetting->phone_number,
                    'body' => "Your HOTESCORT verification code is: $otp"
                ]
            );
            Log::info('Twilio SMS send attempt', [
                'mobile' => $mobile,
                'otp' => $otp
            ]);
        } catch (\Exception $e) {
            Log::error('Twilio SMS error: ' . $e->getMessage(), [
                'mobile' => $mobile,
                'referral_code' => $validated['referral_code'] ?? null,
                'exception' => $e
            ]);
            // Optionally delete the user or cache? Usually keep user but mark as failed.
            return back()->withErrors(['mobile' => $e->getMessage()])->withInput();
        }

        Auth::login($user);

        // Store session flags for OTP verification
        Session::put('otp_required', true);
        Session::put('email', $validated['email']);
        Session::put('phone', $validated['mobile']);

        return redirect('/otp-verification')
            ->with('success', 'Signup successful. Please verify your mobile number.');
    }

    /**
     * Show OTP verification form
     */
    public function otpVerficationForm()
    {
        if (!session()->has('otp_required')) {
            return redirect('/');
        }

        $email = session()->get('email');
        $phone = session()->get('phone');
        $user = User::where('email', $email)->orWhere('mobile', $phone)->first();

        if (!$user) {
            return redirect('/signup')->withErrors(['user' => 'User not found.']);
        }

        // Get remaining time from cache
        $otpKey = 'otp_signup_' . $user->id;
        $remainingTime = Cache::ttl($otpKey); // seconds remaining, or negative if expired
        if ($remainingTime < 0) {
            $remainingTime = 0;
        }

        return view('otp-verification', compact('user', 'remainingTime'));
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'otp' => 'required|numeric|digits:6',
        ]);

        // Ensure user is in session
        if (!session()->has('otp_required')) {
            return redirect('/')->withErrors(['session' => 'OTP session expired.']);
        }

        $email = session()->get('email');
        $phone = session()->get('phone');
        $user = User::where('email', $email)->orWhere('mobile', $phone)->first();

        if (!$user) {
            return redirect('/signup')->withErrors(['user' => 'User not found.']);
        }

        // Rate limiting: max 5 attempts per 10 minutes per user
        $rateLimitKey = 'otp-verify:' . $user->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 5)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return back()->withErrors(['otp' => "Too many attempts. Please try again in {$seconds} seconds."]);
        }
        RateLimiter::hit($rateLimitKey, 600); // 10 minutes

        $otpKey = 'otp_signup_' . $user->id;
        $cached = Cache::get($otpKey);

        if (!$cached) {
            return back()->withErrors(['otp' => 'OTP has expired or is invalid.']);
        }

        // Check attempt count from cache
        if ($cached['attempts'] >= 3) {
            Cache::forget($otpKey); // Lock out after too many attempts
            return back()->withErrors(['otp' => 'Too many incorrect attempts. Please request a new OTP.']);
        }

        // Verify hash
        if (!Hash::check($request->otp, $cached['hash'])) {
            // Increment attempts in cache
            $cached['attempts']++;
            Cache::put($otpKey, $cached, Cache::ttl($otpKey)); // preserve original TTL
            return back()->withErrors(['otp' => 'Invalid OTP code.']);
        }

        // Success: mark mobile as verified, clear cache and session
        $user->mobile_verified = true;
        $user->save();

        Cache::forget($otpKey);
        RateLimiter::clear($rateLimitKey); // Clear rate limiter on success
        Session::forget('otp_required');
        Session::forget('email');
        Session::forget('phone');

        return redirect('/dashboard')->with('success', 'Mobile number verified successfully.');
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request)
    {
        if (!session()->has('otp_required')) {
            return response()->json([
                'success' => false,
                'message' => 'OTP session expired. Please signup again.'
            ]);
        }

        $email = session()->get('email');
        $phone = session()->get('phone');
        $user = User::where('email', $email)
            ->orWhere('mobile', $phone)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found.'
            ]);
        }

        // Rate limiting: max 2 resends per 5 minutes
        $rateLimitKey = 'otp-resend:' . $user->id;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 2)) {
            $seconds = RateLimiter::availableIn($rateLimitKey);
            return response()->json([
                'success' => false,
                'message' => "Too many resend attempts. Please try again in {$seconds} seconds."
            ], 429);
        }
        RateLimiter::hit($rateLimitKey, 300); // 5 minutes

        $otp = rand(100000, 999999);
        $otpHash = Hash::make($otp);
        $otpExpirySeconds = 60; // 60 seconds for resend
        $otpKey = 'otp_signup_' . $user->id;

        Cache::put($otpKey, [
            'hash' => $otpHash,
            'attempts' => 0,
        ], now()->addSeconds($otpExpirySeconds));

        // Send SMS via Twilio
        $twilioSetting = TwilioSetting::first();
        try {
            $client = new Client(
                $twilioSetting->api_sid,
                $twilioSetting->api_secret,
                $twilioSetting->account_sid
            );
            $client->messages->create(
                $user->mobile,
                [
                    'from' => $twilioSetting->phone_number,
                    'body' => "Your HOTESCORT verification code is: $otp"
                ]
            );

            Log::info('Twilio SMS resend attempt', [
                'mobile' => $user->mobile,
                'otp' => $otp
            ]);
        } catch (\Exception $e) {
            Log::error('Twilio SMS resend error', [
                'mobile' => $user->mobile,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to resend OTP.'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'OTP resent successfully.',
            'timer' => $otpExpirySeconds
        ]);
    }

    public function showSigninForm()
    {
        $recaptchaSetting = GoogleRecaptchaSetting::where('is_active', 1)->first();
        return view('signin', compact('recaptchaSetting'));
    }

    public function signin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'g-recaptcha-response' => 'required'
        ]);

        $recaptchaConfig = GoogleRecaptchaSetting::where('is_active', 1)->first();
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
            return back()->withErrors(['recaptcha' => 'reCAPTCHA verification failed'])->withInput();
        }

        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password
        ], $request->remember)) {
            $request->session()->regenerate();
            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Invalid email or password'
        ])->withInput();
    }
}