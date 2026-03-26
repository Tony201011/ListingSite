<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Jobs\SendPasswordResetLinkJob;
use App\Jobs\SendPasswordResetSuccessEmailJob;
use App\Models\SmtpSetting;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;

class PasswordResetController extends Controller
{
    public function showLinkRequestForm()
    {
        return view('reset-password');
    }

    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $activeMailSetting = SmtpSetting::query()
            ->where('is_enabled', true)
            ->latest('updated_at')
            ->first();

        if (! $activeMailSetting) {
            $activeMailSetting = SmtpSetting::query()
                ->latest('updated_at')
                ->first();
        }

        if (! $activeMailSetting) {
            Log::error('Password reset email queue failed: no mail setting found.', [
                'email' => $request->input('email'),
            ]);

            return back()->withErrors([
                'email' => 'Unable to send reset email right now. Please try again later.',
            ])->withInput();
        }

        SendPasswordResetLinkJob::dispatch(
            $request->input('email'),
            $activeMailSetting->id
        );

        Log::info('Password reset email queued', [
            'email' => $request->input('email'),
            'mail_setting_id' => $activeMailSetting->id,
        ]);

        return back()->with('success', 'If your email exists in our system, a password reset link has been queued.');
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('reset-password-form', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(Request $request)
    {
        $request->validate([
            'token' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required', 'confirmed', PasswordRule::min(8)],
        ]);

        $resetUser = null;

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) use (&$resetUser): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                $resetUser = $user;

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            if ($resetUser) {
                $activeMailSetting = SmtpSetting::query()
                    ->where('is_enabled', true)
                    ->latest('updated_at')
                    ->first();

                if (! $activeMailSetting) {
                    $activeMailSetting = SmtpSetting::query()
                        ->latest('updated_at')
                        ->first();
                }

                if ($activeMailSetting) {
                    SendPasswordResetSuccessEmailJob::dispatch(
                        $resetUser->id,
                        $activeMailSetting->id
                    );

                    Log::info('Password reset success email queued', [
                        'email' => $resetUser->email,
                        'mail_setting_id' => $activeMailSetting->id,
                    ]);
                } else {
                    Log::warning('Password reset success email not queued: no mail setting found.', [
                        'email' => $resetUser->email,
                    ]);
                }
            }

            Log::info('Password reset successful', [
                'email' => $request->input('email'),
            ]);

            return redirect()->route('signin')->with('success', 'Password reset successful. Please sign in.');
        }

        Log::warning('Password reset failed', [
            'email' => $request->input('email'),
            'status' => $status,
        ]);

        return back()
            ->with('error', __($status))
            ->withErrors(['email' => [__($status)]])
            ->withInput($request->only('email'));
    }
}
