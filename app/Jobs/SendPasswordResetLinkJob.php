<?php

namespace App\Jobs;

use App\Models\SmtpSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class SendPasswordResetLinkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $email,
        public int $mailSettingId
    ) {}

    public function handle(): void
    {
        $mailSetting = SmtpSetting::find($this->mailSettingId);

        if (! $mailSetting) {
            Log::error('Queued password reset link failed: mail setting not found.', [
                'email' => $this->email,
                'mail_setting_id' => $this->mailSettingId,
            ]);

            return;
        }

        $this->applyDatabaseMailConfiguration($mailSetting);

        try {
            $status = Password::sendResetLink([
                'email' => $this->email,
            ]);

            if ($status === Password::RESET_LINK_SENT) {
                Log::info('Password reset link email sent successfully from queue.', [
                    'email' => $this->email,
                    'status' => $status,
                ]);
            } else {
                Log::warning('Password reset link email not sent from queue.', [
                    'email' => $this->email,
                    'status' => $status,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Queued password reset email transport error', [
                'email' => $this->email,
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
