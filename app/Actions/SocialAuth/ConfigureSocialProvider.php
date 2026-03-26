<?php

namespace App\Actions\SocialAuth;

use App\Models\SocialLoginSetting;

class ConfigureSocialProvider
{
    public function execute(string $provider, SocialLoginSetting $setting): void
    {
        config([
            "services.{$provider}.client_id" => $setting->client_id,
            "services.{$provider}.client_secret" => $setting->client_secret,
            "services.{$provider}.redirect" => $setting->redirect_url,
        ]);
    }
}
