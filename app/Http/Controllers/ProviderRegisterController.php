<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\GoogleRecaptchaSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

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
                'recaptcha' => 'Google reCAPTCHA verification failed. Please try again.'
            ])->withInput();
        }

        $user = User::create([
            'name' => $validated['nickname'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => User::ROLE_PROVIDER,
        ]);

        // Optional: Save extra fields like mobile, suburb into provider profile table

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

        // Get recaptcha keys from DB
        $recaptchaSetting = GoogleRecaptchaSetting::where('is_active', 1)->first();

        if (!$recaptchaSetting) {
            return back()->withErrors(['recaptcha' => 'Recaptcha configuration missing']);
        }

        // Verify Google reCAPTCHA
        $response = Http::asForm()->post(
            'https://www.google.com/recaptcha/api/siteverify',
            [
                'secret' => $recaptchaSetting->secret_key,
                'response' => $request->input('g-recaptcha-response'),
                'remoteip' => $request->ip()
            ]
        );

        $result = $response->json();

        if (!isset($result['success']) || $result['success'] != true) {
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
