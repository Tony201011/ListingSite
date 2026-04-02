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

        if (Auth::attempt([
            'email' => $request->email,
            'password' => $request->password,
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('/my-profile');
        }

        return back()->withErrors([
            'email' => 'Invalid email or password',
        ])->withInput();
    }
}
