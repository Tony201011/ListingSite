<?php

namespace App\Http\Controllers;

use App\Models\User;
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
        return view('signup');
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
        $recaptcha = $request->input('g-recaptcha-response');
        $recaptchaSecret = env('RECAPTCHA_SECRET_KEY');
        $recaptchaResponse = null;
        if ($recaptcha && $recaptchaSecret) {
            $recaptchaResponse = json_decode(file_get_contents(
                'https://www.google.com/recaptcha/api/siteverify?secret=' . $recaptchaSecret . '&response=' . $recaptcha
            ), true);
        }
        if (!$recaptchaResponse || empty($recaptchaResponse['success'])) {
            return back()->withErrors(['recaptcha' => 'Google reCAPTCHA verification failed. Please try again.'])->withInput();
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
}
