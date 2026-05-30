<?php

namespace App\Console\Commands;

use App\Models\OnlineUser;
use App\Models\ProviderOnlineLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RolloverOnlineSessions extends Command
{
    protected $signature = 'online-sessions:rollover';

    protected $description = 'At midnight, close open online-session logs and reopen them so each day has its own records';

    public function handle(): int
    {
        // Use midnight of today in the app timezone as the rollover point.
        $midnight = now()->startOfDay()->utc();

        $onlineProfiles = OnlineUser::query()
            ->where('status', 'online')
            ->with('providerProfile')
            ->get();

        if ($onlineProfiles->isEmpty()) {
            $this->info('No online profiles to roll over.');

            return self::SUCCESS;
        }

        $rolled = 0;

        foreach ($onlineProfiles as $onlineUser) {
            $profile = $onlineUser->providerProfile;

            if (! $profile) {
                continue;
            }

            $openLog = ProviderOnlineLog::query()
                ->where('provider_profile_id', $profile->id)
                ->whereNull('went_offline_at')
                ->latest('went_online_at')
                ->first();

            if (! $openLog) {
                // Profile is online but has no open log — create one starting at midnight.
                ProviderOnlineLog::query()->create([
                    'user_id' => $profile->user_id,
                    'provider_profile_id' => $profile->id,
                    'went_online_at' => $midnight,
                ]);
                $rolled++;

                continue;
            }

            // Close the current session at midnight.
            $openLog->update([
                'went_offline_at' => $midnight,
                'duration_seconds' => max(0, (int) $openLog->went_online_at->diffInSeconds($midnight, true)),
            ]);

            // Start a new session for the new day.
            ProviderOnlineLog::query()->create([
                'user_id' => $profile->user_id,
                'provider_profile_id' => $profile->id,
                'went_online_at' => $midnight,
            ]);

            $rolled++;
        }

        Log::info('Midnight online-session rollover completed', ['rolled_profiles' => $rolled]);
        $this->info("Midnight rollover completed. Rolled over profiles: {$rolled}.");

        return self::SUCCESS;
    }
}
