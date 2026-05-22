<?php

namespace App\Actions;

use App\Models\OnlineUser;
use App\Models\ProviderProfile;

class GetOnlineNowState
{
    public function execute(?ProviderProfile $profile): array
    {
        $onlineStatus = false;
        $expiresAt = null;
        $blockedBalance = false;

        if (! $profile) {
            return compact('onlineStatus', 'expiresAt', 'blockedBalance');
        }

        $onlineUser = $this->getOrCreateOnlineUser($profile->id);

        if ($onlineUser->isDirty()) {
            $onlineUser->save();
        }

        $onlineStatus = $onlineUser->isCurrentlyOnline();
        $blockedBalance = $this->isFreeListingExpiredWithNegativeBalance($profile);

        return compact('onlineStatus', 'expiresAt', 'blockedBalance');
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
