<?php

namespace App\Actions\SocialAuth;

use App\Models\SocialLoginSetting;

class ResolveSocialProviderSetting
{
    /**
     * @var array<string, string>
     */
    private array $driverMap = [
        'google' => 'google',
        'facebook' => 'facebook',
        'twitter' => 'twitter-oauth-2',
    ];

    public function execute(string $provider): array
    {
        abort_unless(array_key_exists($provider, $this->driverMap), 404);

        $setting = SocialLoginSetting::query()
            ->where('provider', $provider)
            ->where('is_enabled', true)
            ->first();

        abort_unless(
            $setting &&
            filled($setting->client_id) &&
            filled($setting->client_secret) &&
            filled($setting->redirect_url),
            404
        );

        return [
            'provider' => $provider,
            'driver' => $this->driverMap[$provider],
            'setting' => $setting,
        ];
    }
}
