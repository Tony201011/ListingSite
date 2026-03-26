<?php

namespace App\Jobs;

use App\Models\SmtpSetting;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPasswordResetSuccessEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $userId,
        public int $mailSettingId
    ) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        $mailSetting = SmtpSetting::find($this->mailSettingId);

        if (! $user || blank($user->email)) {
            Log::warning('Queued password reset success email failed: user missing or email blank.', [
                'user_id' => $this->userId,
            ]);

            return;
        }

        if (! $mailSetting) {
            Log::error('Queued password reset success email failed: mail setting not found.', [
                'user_id' => $this->userId,
                'mail_setting_id' => $this->mailSettingId,
            ]);

            return;
        }

        $this->applyDatabaseMailConfiguration($mailSetting);

        try {
            Mail::send('emails.password-reset-success', [
                'name' => $user->name ?? 'User',
                'email' => $user->email,
                'signinUrl' => route('signin'),
            ], function ($message) use ($user): void {
                $message->to($user->email, $user->name ?? null)
                    ->subject('Your password was changed successfully');
            });

            Log::info('Password reset success email sent from queue', [
                'email' => $user->email,
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send password reset success email from queue', [
                'email' => $user->email,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function applyDatabaseMailConfiguration(SmtpSetting $activeMailSetting): void
    {
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
    }
}
