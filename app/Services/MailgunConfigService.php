<?php

namespace App\Services;

use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Log;

class MailgunConfigService
{
    /**
     * Resolve the active SMTP setting, preferring enabled ones.
     */
    public function resolveSmtpSetting(): ?SmtpSetting
    {
        return SmtpSetting::query()
            ->where('is_enabled', true)
            ->latest('updated_at')
            ->first()
            ?? SmtpSetting::query()
                ->latest('updated_at')
                ->first();
    }

    /**
     * Apply the given SmtpSetting to the runtime mail/mailgun config
     * and reset the mailer pool so subsequent sends use it.
     */
    public function apply(SmtpSetting $setting): void
    {
        $domain = $this->resolveDomain($setting);
        $endpoint = $this->resolveEndpoint($setting);

        $fromAddress = $setting->mail_from_address;

        if (! filled($fromAddress) && filled($domain)) {
            $fromAddress = 'postmaster@'.$domain;
        }

        config([
            'mail.default' => $setting->mail_mailer ?: 'mailgun',
            'mail.mailers.mailgun.transport' => 'mailgun',
            'services.mailgun.domain' => $domain,
            'services.mailgun.secret' => $setting->mailgun_secret,
            'services.mailgun.endpoint' => $endpoint ?: 'api.mailgun.net',
            'services.mailgun.scheme' => 'https',
            'mail.from.address' => $fromAddress ?: config('mail.from.address'),
            'mail.from.name' => $setting->mail_from_name ?: config('app.name'),
        ]);

        app('mail.manager')->forgetMailers();
    }

    /**
     * Convenience: resolve + apply + log.  Returns false if no setting found.
     */
    public function applyOrFail(string $context, array $logContext = []): ?SmtpSetting
    {
        $setting = $this->resolveSmtpSetting();

        if (! $setting) {
            Log::error("{$context}: no mail setting found.", $logContext);

            return null;
        }

        if (! $setting->is_enabled) {
            Log::warning("{$context}: using latest mail setting that is disabled.", [
                ...$logContext,
                'mail_setting_id' => $setting->id,
            ]);
        }

        $this->apply($setting);

        Log::info("{$context}: mail configured", [
            ...$logContext,
            'mail_setting_id' => $setting->id,
            'mail_setting_enabled' => (bool) $setting->is_enabled,
            'mailer_used' => config('mail.default'),
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            'mailgun_domain' => config('services.mailgun.domain'),
            'mailgun_endpoint' => config('services.mailgun.endpoint'),
            'mailgun_secret_present' => filled(config('services.mailgun.secret')),
        ]);

        return $setting;
    }

    private function resolveDomain(SmtpSetting $setting): ?string
    {
        $sandboxDomain = $setting->mailgun_sandbox_domain ?: $setting->mailgun_domain;
        $liveDomain = $setting->mailgun_live_domain;

        $domain = $setting->use_mailgun_sandbox
            ? $sandboxDomain
            : ($liveDomain ?: $sandboxDomain);

        if (filled($domain)) {
            $domain = preg_replace('#^https?://#i', '', rtrim(trim($domain), '/'));
        }

        return $domain;
    }

    private function resolveEndpoint(SmtpSetting $setting): ?string
    {
        $endpoint = $setting->mailgun_endpoint ?: 'api.mailgun.net';

        if (filled($endpoint)) {
            $endpoint = parse_url(trim($endpoint), PHP_URL_HOST)
                ?: preg_replace('#^https?://#i', '', rtrim(trim($endpoint), '/'));
        }

        return $endpoint;
    }
}
