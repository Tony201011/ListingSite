<?php

namespace App\Actions\Auth;

use App\Models\SmtpSetting;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendProviderAccountEmails
{
    public function execute(User $user): void
    {
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
            Log::error('Signup emails failed: no active mail setting found.', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return;
        }

        if (! $activeMailSetting->is_enabled) {
            Log::warning('Signup emails using latest mail setting that is disabled.', [
                'user_id' => $user->id,
                'email' => $user->email,
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

        $fromAddress = $activeMailSetting->mail_from_address;

        if (! filled($fromAddress) && filled($mailgunDomain)) {
            $fromAddress = 'postmaster@' . $mailgunDomain;
        }

        config([
            'mail.default' => $activeMailSetting->mail_mailer ?: 'mailgun',
            'mail.mailers.mailgun.transport' => 'mailgun',
            'services.mailgun.domain' => $mailgunDomain,
            'services.mailgun.secret' => $activeMailSetting->mailgun_secret,
            'services.mailgun.endpoint' => $mailgunEndpoint ?: 'api.mailgun.net',
            'services.mailgun.scheme' => 'https',
            'mail.from.address' => $fromAddress ?: config('mail.from.address'),
            'mail.from.name' => $activeMailSetting->mail_from_name ?: config('app.name'),
        ]);

        app('mail.manager')->forgetMailers();

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->id,
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        try {
            Mail::mailer('mailgun')->send(
                'emails.verify-email',
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'verificationUrl' => $verificationUrl,
                ],
                function ($message) use ($user): void {
                    $message->to($user->email)
                        ->subject('Verify Your Email Address');
                }
            );
        } catch (\Throwable $e) {
            Log::error('Verification email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }

        try {
            Mail::mailer('mailgun')->send(
                'emails.account-created',
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'signinUrl' => url('/signin'),
                ],
                function ($message) use ($user): void {
                    $message->to($user->email)
                        ->subject('Your account has been created');
                }
            );
        } catch (\Throwable $e) {
            Log::error('Account created email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
