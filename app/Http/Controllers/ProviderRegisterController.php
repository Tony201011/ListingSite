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

class ProviderRegisterController extends Controller
{
    /**
     * Show signup form
     */
    public function showSignupForm()
    {
        $recaptchaSetting = GoogleRecaptchaSetting::where('is_active', 1)->first();
        //dd($recaptchaSetting);
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
        $otp = rand(100000, 999999);
        $mobile = $validated['mobile'];
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
            return back()->withErrors(['mobile' => $e->getMessage()])->withInput();
        }
        $user = User::create([
            'name' => $validated['nickname'],
            'email' => $validated['email'],
            'mobile' => $mobile,
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_PROVIDER,
            'otp' => $otp,
            'otp_expires_at' => now()->addSeconds(120),
            'mobile_verified' => false,
            'referral_code' => $validated['referral_code'] ?? null
        ]);



        Auth::login($user);

        Session::put('otp_required', true);
        Session::put('email', $validated['email']);
        Session::put('phone', $validated['phone']);

        return redirect('/otp-verification')
            ->with('success', 'Signup successful. Please verify your mobile number.');
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

        // Attempt login
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

        if (!session()->has('otp_required')) {
            return redirect('/');
        }

        $email = session()->get('email');
        $phone = session()->get('phone');
        $userData = User::where('email', $email)->orWhere('mobile', $phone)->first();
        if ($userData->otp_expires_at) {
            $remainingTime = now()->diffInSeconds($userData->otp_expires_at, false);
            $remainingTime = $remainingTime > 0 ? $remainingTime : 0;
        }

        if (!$userData) {
            return redirect('/signup')->withErrors(['user' => 'User not found.']);
        }
        return view('otp-verification', compact('userData', 'remainingTime'));
    }

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

        $otp = rand(100000, 999999);

        // OTP expiry time
        $otpExpirySeconds = 60;

        $user->otp = $otp;
        $user->otp_expires_at = now()->addSeconds($otpExpirySeconds);
        $user->save();

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
}
