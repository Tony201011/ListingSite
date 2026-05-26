<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\OnlineUser;
use App\Models\ProviderOnlineLog;
use App\Models\ProviderProfile;

class UpdateOnlineNowStatus
{
    public function execute(ProviderProfile $profile, ?string $status): ActionResult
    {
        $onlineUser = $this->getOrCreateOnlineUser($profile);

        if ($status === 'online') {
            return $this->goOnline($onlineUser, $profile);
        }

        return $this->goOffline($onlineUser, $profile);
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

        if ($onlineUser->user_id !== $profile->user_id) {
            $onlineUser->user_id = $profile->user_id;
            $onlineUser->save();
        }

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
            $this->ensureOpenSessionLog($profile);

            return ActionResult::success([
                'status' => 'online',
                'expires_at' => null,
                'online_started_at' => $onlineUser->online_started_at?->toIso8601String(),
            ], 'You are already online.');
        }

        $this->closeOpenSessionLogs($profile);

        $onlineUser->status = 'online';
        $onlineUser->usage_date = today();
        $onlineUser->usage_count += 1;
        $onlineUser->online_started_at = now();
        $onlineUser->online_expires_at = null;
        $onlineUser->save();

        $this->ensureOpenSessionLog($profile);

        return ActionResult::success([
            'status' => 'online',
            'expires_at' => null,
            'online_started_at' => $onlineUser->online_started_at->toIso8601String(),
        ], 'Online Now enabled.');
    }

    private function goOffline(OnlineUser $onlineUser, ProviderProfile $profile): ActionResult
    {
        $onlineUser->status = 'offline';
        $onlineUser->online_started_at = null;
        $onlineUser->online_expires_at = null;
        $onlineUser->save();

        $this->closeOpenSessionLogs($profile);

        return ActionResult::success([
            'status' => 'offline',
            'expires_at' => null,
        ], 'Online Now disabled.');
    }

    private function ensureOpenSessionLog(ProviderProfile $profile): void
    {
        $openLog = ProviderOnlineLog::query()
            ->where('provider_profile_id', $profile->id)
            ->whereNull('went_offline_at')
            ->latest('went_online_at')
            ->first();

        if ($openLog) {
            return;
        }

        ProviderOnlineLog::query()->create([
            'user_id' => $profile->user_id,
            'provider_profile_id' => $profile->id,
            'went_online_at' => now(),
        ]);
    }

    private function closeOpenSessionLogs(ProviderProfile $profile): void
    {
        $closedAt = now();

        ProviderOnlineLog::query()
            ->where('provider_profile_id', $profile->id)
            ->whereNull('went_offline_at')
            ->get()
            ->each(function (ProviderOnlineLog $log) use ($closedAt): void {
                $log->update([
                    'went_offline_at' => $closedAt,
                    'duration_seconds' => max(0, (int) $log->went_online_at->diffInSeconds($closedAt, true)),
                ]);
            });
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
