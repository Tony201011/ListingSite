<?php

namespace App\Actions;

use App\Models\OnlineUser;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;

class GetOnlineNowState
{
    public function execute(?ProviderProfile $profile): array
    {
        $settings = SiteSetting::getStatusSettings();
        $maxUses = $settings['online_status_max_uses'];

        $onlineStatus = false;
        $remainingUses = $maxUses;
        $expiresAt = null;
        $blockedBalance = false;

        if (! $profile) {
            return compact('onlineStatus', 'remainingUses', 'expiresAt', 'blockedBalance');
        }

        $onlineUser = $this->getOrCreateOnlineUser($profile->id);

        $this->expireIfNeeded($onlineUser);

        if ($onlineUser->isDirty()) {
            $onlineUser->save();
        }

        $onlineStatus = $onlineUser->isCurrentlyOnline();
        $remainingUses = max(0, $maxUses - $onlineUser->usage_count);
        $expiresAt = optional($onlineUser->online_expires_at)?->toIso8601String();
        $blockedBalance = $this->isFreeListingExpiredWithNegativeBalance($profile);

        return compact('onlineStatus', 'remainingUses', 'expiresAt', 'blockedBalance');
    }

    private function getOrCreateOnlineUser(int $profileId): OnlineUser
    {
        $onlineUser = OnlineUser::firstOrCreate(
            ['provider_profile_id' => $profileId],
            [
                'status' => 'offline',
                'usage_date' => today(),
                'usage_count' => 0,
            ]
        );

        $onlineUser->resetDailyUsageIfNeeded();

        return $onlineUser;
    }

    private function expireIfNeeded(OnlineUser $onlineUser): void
    {
        if (
            $onlineUser->status === 'online' &&
            $onlineUser->online_expires_at &&
            now()->greaterThanOrEqualTo($onlineUser->online_expires_at)
        ) {
            $onlineUser->status = 'offline';
            $onlineUser->online_started_at = null;
            $onlineUser->online_expires_at = null;
        }
    }

    private function isFreeListingExpiredWithNegativeBalance(ProviderProfile $profile): bool
    {
        $expiredAt = $profile->free_listing_expires_at;

        if ($expiredAt !== null && $expiredAt->isFuture()) {
            return false;
        }

        $profile->loadMissing('user');

        return $profile->user !== null && $profile->user->credits < 0;
    }
}
