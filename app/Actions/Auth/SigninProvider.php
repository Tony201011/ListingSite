<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class SigninProvider
{
    public function execute(Request $request): RedirectResponse
    {
        $user = User::query()->where('email', $request->email)->first();

        if (! $user) {
            $trashedUser = User::withTrashed()->where('email', $request->email)->first();

            if (
                $trashedUser
                && $trashedUser->trashed()
                && $trashedUser->account_status === 'soft_deleted'
                && $trashedUser->scheduled_purge_at?->isFuture()
                && Hash::check((string) $request->password, (string) $trashedUser->password)
            ) {
                $request->session()->put('restore_candidate_user_id', $trashedUser->id);

                return back()->withErrors([
                    'email' => 'This account has been deleted and is currently within the restoration period.',
                ])->withInput($request->only('email'))->with('show_restore_account', true);
            }

            return back()->withErrors([
                'email' => 'Invalid email or password',
            ])->withInput();
        }

        if ($user->is_blocked) {
            return back()->withErrors([
                'email' => 'Your account has been blocked.',
            ])->withInput();
        }

        $guard = $user->role === User::ROLE_ADMIN ? 'admin' : 'web';

        if (Auth::guard($guard)->attempt([
            'email' => $request->email,
            'password' => $request->password,
        ], $request->boolean('remember'))) {
            $request->session()->regenerate();

            if (! $user->hasVerifiedEmail()) {
                return redirect()->route('verification.notice');
            }

            $destination = match ($user->role) {
                User::ROLE_ADMIN => '/admin',
                User::ROLE_REVIEWER => '/my-listings',
                default => '/my-profiles',
            };

            return redirect()->intended($destination);
        }

        return back()->withErrors([
            'email' => 'Invalid email or password',
        ])->withInput();
    }
}
