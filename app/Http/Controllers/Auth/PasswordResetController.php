<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Http\Requests\Auth\SendPasswordResetLinkRequest;
use App\Jobs\SendPasswordResetLinkJob;
use App\Jobs\SendPasswordResetSuccessEmailJob;
use App\Services\Mail\ActiveMailSettingService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    public function __construct(
        private ActiveMailSettingService $mailSettingService
    ) {
    }

    public function showLinkRequestForm()
    {
        return view('auth.reset-password');
    }

    public function sendResetLinkEmail(SendPasswordResetLinkRequest $request)
    {
        $email = $request->validated('email');
        $activeMailSetting = $this->mailSettingService->getActiveOrLatest();

        if (! $activeMailSetting) {
            Log::error('Password reset email queue failed: no mail setting found.', [
                'email' => $email,
            ]);

            return back()->withErrors([
                'email' => 'Unable to send reset email right now. Please try again later.',
            ])->withInput();
        }

        SendPasswordResetLinkJob::dispatch($email, $activeMailSetting->id);

        Log::info('Password reset email queued', [
            'email' => $email,
            'mail_setting_id' => $activeMailSetting->id,
        ]);

        return back()->with(
            'success',
            'If your email exists in our system, a password reset link has been queued.'
        );
    }

    public function showResetForm(Request $request, string $token)
    {
        return view('auth.reset-password-form', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function reset(ResetPasswordRequest $request)
    {
        $validated = $request->validated();
        $resetUser = null;

        $status = Password::reset(
            [
                'email' => $validated['email'],
                'password' => $validated['password'],
                'password_confirmation' => $validated['password_confirmation'],
                'token' => $validated['token'],
            ],
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
                $activeMailSetting = $this->mailSettingService->getActiveOrLatest();

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
                'email' => $validated['email'],
            ]);

            return redirect()
                ->route('signin')
                ->with('success', 'Password reset successful. Please sign in.');
        }

        Log::warning('Password reset failed', [
            'email' => $validated['email'],
            'status' => $status,
        ]);

        return back()
            ->with('error', __($status))
            ->withErrors(['email' => [__($status)]])
            ->withInput([
                'email' => $validated['email'],
            ]);
    }
}
