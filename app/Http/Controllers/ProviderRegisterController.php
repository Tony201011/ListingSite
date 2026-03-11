<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\GoogleRecaptchaSetting;
use App\Models\TwilioSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Twilio\Rest\Client;
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
            'password' => 'required|string|min:8|confirmed',
            'mobile' => ['required', 'regex:/^(04\d{8}|614\d{8})$/'],
            'suburb' => 'required|string|max:255',
            'age_confirm' => 'accepted',
            'g-recaptcha-response' => 'required',
        ]);

        // Google reCAPTCHA server-side validation
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
        $otp = rand(100000, 999999);

        $mobile = $validated['mobile'];

        if (str_starts_with($mobile, '04')) {
                $mobile = '+61' . substr($mobile, 1);
        }

        $twilioSetting = TwilioSetting::first();

    try {

        $client = new Client(
            $twilioSetting->account_sid,
            $twilioSetting->api_secret,
            $twilioSetting->api_sid
        );

        $client->messages->create(
            $mobile,
            [
                'from' => $twilioSetting->phone_number,
                'body' => "Your HOTESCORT verification code is: $otp"
            ]
        );

    } catch (\Exception $e) {
        return back()->withErrors(['mobile' => $e->getMessage()])->withInput();
    }
        $user = User::create([
            'name' => $validated['nickname'],
            'email' => $validated['email'],
            'mobile' => $mobile,
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_PROVIDER,
            'otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
            'mobile_verified' => false
        ]);


        Auth::login($user);

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
}
