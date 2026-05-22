<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\OnlineUser;
use App\Models\ProviderProfile;


class UpdateOnlineNowStatus
{
    public function execute(ProviderProfile $profile, ?string $status): ActionResult
    {
        $onlineUser = $this->getOrCreateOnlineUser($profile);

        if ($status === 'online') {
            return $this->goOnline($onlineUser, $profile);
        }

        return $this->goOffline($onlineUser);
    }

    private function getOrCreateOnlineUser(ProviderProfile $profile): OnlineUser
    {
        $onlineUser = OnlineUser::firstOrCreate(
            ['provider_profile_id' => $profile->id],
            [
                'user_id' => $profile->user_id,
                'status' => 'offline',
                'usage_date' => today(),
                'usage_count' => 0,
            ]
        );

        $onlineUser->resetDailyUsageIfNeeded();

        return $onlineUser;
    }

    private function goOnline(OnlineUser $onlineUser, ProviderProfile $profile): ActionResult
    {
        if ($this->isFreeListingExpiredWithNegativeBalance($profile)) {
            return new ActionResult(
                false,
                422,
                'Your 21-day period has expired and your account balance is negative. Please clear your balance to go online or become available now.',
                ['status' => 'offline'],
                'domain'
            );
        }

        if ($onlineUser->isCurrentlyOnline()) {
            return ActionResult::success([
                'status' => 'online',
                'expires_at' => null,
            ], 'You are already online.');
        }

        $onlineUser->status = 'online';
        $onlineUser->usage_date = today();
        $onlineUser->online_started_at = now();
        $onlineUser->online_expires_at = null;
        $onlineUser->save();

        return ActionResult::success([
            'status' => 'online',
            'expires_at' => null,
        ], 'Online Now enabled.');
    }

    private function goOffline(OnlineUser $onlineUser): ActionResult
    {
        $onlineUser->status = 'offline';
        $onlineUser->online_started_at = null;
        $onlineUser->online_expires_at = null;
        $onlineUser->save();

        return ActionResult::success([
            'status' => 'offline',
            'expires_at' => null,
        ], 'Online Now disabled.');
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
