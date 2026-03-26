<?php

namespace App\Actions\SocialAuth;

use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;

class RedirectToSocialProvider
{
    public function __construct(
        private ResolveSocialProviderSetting $resolveSocialProviderSetting,
        private ConfigureSocialProvider $configureSocialProvider
    ) {
    }

    public function execute(string $provider): RedirectResponse
    {
        $resolved = $this->resolveSocialProviderSetting->execute($provider);

        $this->configureSocialProvider->execute(
            $resolved['provider'],
            $resolved['setting']
        );

        return Socialite::driver($resolved['driver'])->redirect();
    }
}
