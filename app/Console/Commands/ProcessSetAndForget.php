<?php

namespace App\Console\Commands;

use App\Actions\UpdateAvailableNowStatus;
use App\Actions\UpdateOnlineNowStatus;
use App\Models\ProviderProfile;
use App\Models\SetAndForget;
use Illuminate\Console\Command;

class ProcessSetAndForget extends Command
{
    protected $signature = 'set-and-forget:process';

    protected $description = 'Process Set & Forget automations and activate Online Now / Available Now on schedule';

    public function __construct(
        private UpdateOnlineNowStatus $updateOnlineNowStatus,
        private UpdateAvailableNowStatus $updateAvailableNowStatus
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $triggered = 0;
        $failed = 0;

        SetAndForget::query()
            ->where(function ($q) {
                $q->where('online_now_enabled', true)
                    ->orWhere('available_now_enabled', true);
            })
            ->with('providerProfile')
            ->chunkById(100, function ($schedules) use (&$triggered, &$failed) {
                foreach ($schedules as $schedule) {
                    $profile = $schedule->providerProfile;

                    if (! $profile instanceof ProviderProfile) {
                        continue;
                    }

                    if ($schedule->shouldTriggerOnlineNow()) {
                        $result = $this->updateOnlineNowStatus->execute($profile, 'online');

                        if ($result->isSuccess()) {
                            $triggered++;
                            $this->info("Online Now triggered for profile ID {$profile->id}");
                        } else {
                            $failed++;
                            $this->warn("Online Now failed for profile ID {$profile->id}: {$result->message()}");
                        }
                    }

                    if ($schedule->shouldTriggerAvailableNow()) {
                        $result = $this->updateAvailableNowStatus->execute($profile, 'online');

                        if ($result->isSuccess()) {
                            $triggered++;
                            $this->info("Available Now triggered for profile ID {$profile->id}");
                        } else {
                            $failed++;
                            $this->warn("Available Now failed for profile ID {$profile->id}: {$result->message()}");
                        }
                    }
                }
            });

        $this->info("Set & Forget complete: {$triggered} triggered, {$failed} skipped/failed.");

        return self::SUCCESS;
    }
}
