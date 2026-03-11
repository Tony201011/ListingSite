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
use Illuminate\Support\Facades\Cache; // <-- Import Cache

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
            'password' => 'required|min:8|confirmed',
            'mobile' => ['required', 'regex:/^\+61\d{9}$/', 'unique:users,mobile'],
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
        if (!session()->has('otp_required') || !session()->has('pending_signup_key')) {
            return response()->json([
                'success' => false,
                'message' => 'OTP session expired. Please signup again.'
            ]);
        }

        $pendingKey = session()->get('pending_signup_key');
        $pendingUser = Cache::get($pendingKey);

        if (!$pendingUser) {
            return response()->json([
                'success' => false,
                'message' => 'Signup session expired. Please signup again.'
            ]);
        }

        $otp = random_int(100000, 999999);
        $otpExpirySeconds = 60;
        $otpExpiresAt = now()->addSeconds($otpExpirySeconds);
        Cache::put($pendingKey . '_otp', [
            'code' => $otp,
            'expires_at' => $otpExpiresAt->timestamp
        ], $otpExpiresAt);

        $twilioSetting = TwilioSetting::first();

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

            Log::info('Twilio SMS resend attempt', [
                'mobile' => $pendingUser['mobile'],
            ]);
        } catch (\Exception $e) {
            Log::error('Twilio SMS resend error', [
                'mobile' => $pendingUser['mobile'],
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


}