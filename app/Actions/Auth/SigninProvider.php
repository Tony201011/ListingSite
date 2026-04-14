<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SigninProvider
{
    public function execute(Request $request): RedirectResponse
    {
        $user = User::query()->where('email', $request->email)->first();

        if (! $user) {
            return back()->withErrors([
                'email' => 'Invalid email or password',
            ])->withInput();
        }

        if ($user->is_blocked) {
            return back()->withErrors([
                'email' => 'Your account has been blocked.',
            ])->withInput();
        }

        if (! $user->email_verified_at) {
            return back()->withErrors([
                'email' => 'Please verify your email address before logging in.',
            ])->withInput();
        }

        $guard = 'web';

        if (Auth::guard($guard)->attempt([
            'email' => $request->email,
            'password' => $request->password,
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();

            $destination = match ($user->role) {
                User::ROLE_ADMIN => '/admin',
                default => '/my-profile',
            };

            return redirect()->intended($destination);
        }

        return back()->withErrors([
            'email' => 'Invalid email or password',
        ])->withInput();
    }
}
