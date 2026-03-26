<?php

namespace App\Actions;

use App\Models\OnlineUser;
use App\Models\User;

class GetOnlineNowState
{
    public function execute(?User $user): array
    {
        $onlineStatus = false;
        $remainingUses = 4;
        $expiresAt = null;

        if (! $user) {
            return compact('onlineStatus', 'remainingUses', 'expiresAt');
        }

        $onlineUser = $this->getOrCreateOnlineUser($user->id);

        $this->expireIfNeeded($onlineUser);
        $onlineUser->save();

        $onlineStatus = $onlineUser->isCurrentlyOnline();
        $remainingUses = max(0, 4 - $onlineUser->usage_count);
        $expiresAt = optional($onlineUser->online_expires_at)?->toIso8601String();

        return compact('onlineStatus', 'remainingUses', 'expiresAt');
    }

    private function getOrCreateOnlineUser(int $userId): OnlineUser
    {
        $onlineUser = OnlineUser::firstOrCreate(
            ['user_id' => $userId],
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
}
