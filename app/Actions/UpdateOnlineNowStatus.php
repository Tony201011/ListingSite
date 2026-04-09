<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\OnlineUser;
use App\Models\User;

class UpdateOnlineNowStatus
{
    public function execute(User $user, ?string $status): ActionResult
    {
        $onlineUser = $this->getOrCreateOnlineUser($user->id);

        $this->expireIfNeeded($onlineUser);

        if ($status === 'online') {
            return $this->goOnline($onlineUser);
        }

        return $this->goOffline($onlineUser);
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
            $onlineUser->save();
        }
    }

    private function goOnline(OnlineUser $onlineUser): ActionResult
    {
        if ($onlineUser->isCurrentlyOnline()) {
            return ActionResult::success([
                'status' => 'online',
                'remaining_uses' => max(0, 4 - $onlineUser->usage_count),
                'expires_at' => optional($onlineUser->online_expires_at)?->toIso8601String(),
            ], 'You are already online.');
        }

        if ($onlineUser->usage_count >= 4) {
            return new ActionResult(
                false,
                422,
                'You have already used Online Now 4 times today.',
                [
                    'status' => 'offline',
                    'remaining_uses' => 0,
                ],
                'domain'
            );
        }

        $onlineUser->status = 'online';
        $onlineUser->usage_date = today();
        $onlineUser->usage_count += 1;
        $onlineUser->online_started_at = now();
        $onlineUser->online_expires_at = now()->addMinutes(60);
        $onlineUser->save();

        return ActionResult::success([
            'status' => 'online',
            'remaining_uses' => max(0, 4 - $onlineUser->usage_count),
            'expires_at' => optional($onlineUser->online_expires_at)?->toIso8601String(),
        ], 'Online Now enabled for 60 minutes.');
    }

    private function goOffline(OnlineUser $onlineUser): ActionResult
    {
        $onlineUser->status = 'offline';
        $onlineUser->online_started_at = null;
        $onlineUser->online_expires_at = null;
        $onlineUser->save();

        return ActionResult::success([
            'status' => 'offline',
            'remaining_uses' => max(0, 4 - $onlineUser->usage_count),
            'expires_at' => null,
        ], 'Online Now disabled.');
    }
}
