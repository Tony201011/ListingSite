<?php

namespace App\Filament\Resources\SmtpSettings\Widgets;

use App\Models\SmtpSetting;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MailSettingsStats extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $setting = SmtpSetting::query()->latest('updated_at')->first();

        if (! $setting) {
            return [
                Stat::make('Mail Settings', 'Not Configured')
                    ->description('Add a mail setting to enable stats')
                    ->color('warning'),
                Stat::make('Mailer', 'N/A')
                    ->description('No active mail configuration')
                    ->color('gray'),
                Stat::make('Total Mail Sent', '0')
                    ->description('Mailgun API')
                    ->color('gray'),
                Stat::make('Total Mail Failed', '0')
                    ->description('Mailgun API')
                    ->color('gray'),
                Stat::make('Accepted (Today)', '0')
                    ->description('Mailgun API')
                    ->color('gray'),
                Stat::make('Delivered (Today)', '0')
                    ->description('Mailgun API')
                    ->color('gray'),
            ];
        }

        $domain = $setting->use_mailgun_sandbox
            ? ($setting->mailgun_sandbox_domain ?: $setting->mailgun_domain)
            : ($setting->mailgun_live_domain ?: $setting->mailgun_sandbox_domain ?: $setting->mailgun_domain);

        $endpoint = $this->normalizeEndpoint($setting->mailgun_endpoint ?: 'api.mailgun.net');
        $domain = $this->normalizeHost($domain);

        $accepted = 0;
        $delivered = 0;
        $failed = 0;

        if (filled($setting->mailgun_secret) && filled($domain) && filled($endpoint)) {
            try {
                $response = Http::withBasicAuth('api', (string) $setting->mailgun_secret)
                    ->timeout(15)
                    ->get("https://{$endpoint}/v3/{$domain}/stats/total", [
                        'event' => ['accepted', 'delivered', 'failed'],
                        'duration' => '24h',
                    ]);

                if ($response->successful()) {
                    $stats = (array) data_get($response->json(), 'stats', []);

                    foreach ($stats as $row) {
                        $accepted += (int) data_get($row, 'accepted.total', 0);
                        $delivered += (int) data_get($row, 'delivered.total', 0);
                        $failed += (int) data_get($row, 'failed.total', 0);
                    }
                } else {
                    Log::warning('Mail settings stats request failed', [
                        'status' => $response->status(),
                        'endpoint' => $endpoint,
                        'domain' => $domain,
                    ]);
                }
            } catch (\Throwable $e) {
                Log::error('Mail settings stats exception', [
                    'error' => $e->getMessage(),
                    'endpoint' => $endpoint,
                    'domain' => $domain,
                ]);
            }
        }

        return [
            Stat::make('Mail Settings', $setting->is_enabled ? 'Enabled' : 'Disabled')
                ->description($setting->use_mailgun_sandbox ? 'Sandbox Domain' : 'Live Domain')
                ->color($setting->is_enabled ? 'success' : 'warning'),
            Stat::make('Mailer', strtoupper((string) ($setting->mail_mailer ?: 'mailgun')))
                ->description($domain ?: 'Domain not configured')
                ->color('info'),
            Stat::make('Total Mail Sent', number_format($accepted))
                ->description('Mailgun API (24h)')
                ->color('success'),
            Stat::make('Total Mail Failed', number_format($failed))
                ->description('Mailgun API (24h)')
                ->color('danger'),
            Stat::make('Accepted (Today)', number_format($accepted))
                ->description('Mailgun API (24h)')
                ->color('success'),
            Stat::make('Delivered (Today)', number_format($delivered))
                ->description('Mailgun API (24h)')
                ->color('success'),
        ];
    }

    private function normalizeHost(?string $host): ?string
    {
        if (blank($host)) {
            return null;
        }

        return preg_replace('#^https?://#i', '', rtrim(trim((string) $host), '/'));
    }

    private function normalizeEndpoint(?string $endpoint): ?string
    {
        if (blank($endpoint)) {
            return null;
        }

        $clean = trim((string) $endpoint);

        return parse_url($clean, PHP_URL_HOST)
            ?: preg_replace('#^https?://#i', '', rtrim($clean, '/'));
    }
}
