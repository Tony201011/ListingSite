<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SmtpSetting;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
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

        $activeMailSetting = $this->applyDatabaseMailConfiguration();

        Log::info('Password reset email attempt', [
            'email' => $request->input('email'),
            'db_mail_setting_found' => (bool) $activeMailSetting,
            'mailer_default' => (string) config('mail.default', 'log'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            'mailgun_domain' => config('services.mailgun.domain'),
            'mailgun_endpoint' => config('services.mailgun.endpoint'),
            'mailgun_secret_present' => filled(config('services.mailgun.secret')),
        ]);

        try {
            $status = Password::sendResetLink(
                $request->only('email')
            );
        } catch (\Throwable $e) {
            Log::error('Password reset email transport error', [
                'email' => $request->input('email'),
                'mailer_default' => (string) config('mail.default', 'log'),
                'mailgun_domain' => config('services.mailgun.domain'),
                'mailgun_endpoint' => config('services.mailgun.endpoint'),
                'mailgun_secret_present' => filled(config('services.mailgun.secret')),
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'email' => 'Unable to send reset email right now. Please try again later.',
            ])->withInput();
        }

        if ($status === Password::RESET_LINK_SENT) {
            Log::info('Password reset email sent successfully', [
                'email' => $request->input('email'),
                'status' => $status,
            ]);
        } else {
            Log::warning('Password reset email not sent', [
                'email' => $request->input('email'),
                'status' => $status,
            ]);
        }

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', __($status))
            : back()->withErrors(['email' => __($status)]);
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
            $this->sendPasswordResetSuccessEmail($resetUser);

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

    private function sendPasswordResetSuccessEmail(mixed $user): void
    {
        if (! $user || blank($user->email ?? null)) {
            return;
        }

        $this->applyDatabaseMailConfiguration();

        try {
            Mail::send('emails.password-reset-success', [
                'name' => $user->name ?? 'User',
                'email' => $user->email,
                'signinUrl' => route('signin'),
            ], function ($message) use ($user): void {
                $message->to($user->email, $user->name ?? null)
                    ->subject('Your password was changed successfully');
            });

            Log::info('Password reset success email sent', [
                'email' => $user->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send password reset success email', [
                'email' => $user->email,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function applyDatabaseMailConfiguration(): ?SmtpSetting
    {
        $activeMailSetting = SmtpSetting::query()
            ->where('is_enabled', true)
            ->latest('updated_at')
            ->first();

        if (! $activeMailSetting) {
            // Fallback to latest row so reset mail can still work when admin forgot to toggle enabled.
            $activeMailSetting = SmtpSetting::query()
                ->latest('updated_at')
                ->first();
        }

        if (! $activeMailSetting) {
            return null;
        }

        if (! $activeMailSetting->is_enabled) {
            Log::warning('Password reset using latest mail setting that is disabled.', [
                'mail_setting_id' => $activeMailSetting->id,
            ]);
        }

        $sandboxDomain = $activeMailSetting->mailgun_sandbox_domain ?: $activeMailSetting->mailgun_domain;
        $liveDomain = $activeMailSetting->mailgun_live_domain;
        $mailgunDomain = $activeMailSetting->use_mailgun_sandbox
            ? $sandboxDomain
            : ($liveDomain ?: $sandboxDomain);

        $mailgunEndpoint = $activeMailSetting->mailgun_endpoint ?: 'api.mailgun.net';

        if (filled($mailgunDomain)) {
            $mailgunDomain = preg_replace('#^https?://#i', '', rtrim(trim($mailgunDomain), '/'));
        }

        if (filled($mailgunEndpoint)) {
            $mailgunEndpoint = parse_url(trim($mailgunEndpoint), PHP_URL_HOST)
                ?: preg_replace('#^https?://#i', '', rtrim(trim($mailgunEndpoint), '/'));
        }

        config([
            'mail.default' => $activeMailSetting->mail_mailer ?: 'mailgun',
            'mail.mailers.mailgun.transport' => 'mailgun',
            'services.mailgun.domain' => $mailgunDomain,
            'services.mailgun.secret' => $activeMailSetting->mailgun_secret,
            'services.mailgun.endpoint' => $mailgunEndpoint ?: 'api.mailgun.net',
            'services.mailgun.scheme' => 'https',
            'mail.from.address' => $activeMailSetting->mail_from_address ?: config('mail.from.address'),
            'mail.from.name' => $activeMailSetting->mail_from_name ?: config('mail.from.name'),
        ]);

        app('mail.manager')->forgetMailers();

        return $activeMailSetting;
    }
}
