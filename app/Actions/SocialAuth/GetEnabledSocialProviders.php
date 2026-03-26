<?php

namespace App\Actions\SocialAuth;

use App\Models\SocialLoginSetting;
use Illuminate\Database\Eloquent\Collection;

class GetEnabledSocialProviders
{
    /**
     * @var array<string, string>
     */
    private array $driverMap = [
        'google' => 'google',
        'facebook' => 'facebook',
        'twitter' => 'twitter-oauth-2',
    ];

    public function execute(): Collection
    {
        return SocialLoginSetting::query()
            ->where('is_enabled', true)
            ->whereIn('provider', array_keys($this->driverMap))
            ->orderByRaw("FIELD(provider, 'google', 'facebook', 'twitter')")
            ->get(['provider']);
    }
}
