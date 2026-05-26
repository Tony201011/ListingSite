<?php

namespace App\Listeners;

use App\Models\LoginLog;
use App\Models\OnlineUser;
use App\Models\ProviderOnlineLog;
use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\Schema;

class RecordUserLogout
{
    public function handle(Logout $event): void
    {
        if (app()->runningInConsole() || ! request()->hasSession()) {
            return;
        }

        $userId = $event->user?->getKey();
        if (! $userId) {
            return;
        }

        if (Schema::hasColumns('login_logs', ['logged_out_at', 'duration_seconds'])) {
            $logId = request()->session()->get('login_log_id');

            $log = $logId
                ? LoginLog::where('id', $logId)->where('user_id', $userId)->whereNull('logged_out_at')->first()
                : LoginLog::where('user_id', $userId)->whereNull('logged_out_at')->latest()->first();

            if ($log) {
                $loggedOutAt = now();
                $durationSeconds = max(0, (int) $loggedOutAt->diffInSeconds($log->created_at));

                $log->update([
                    'logged_out_at' => $loggedOutAt,
                    'duration_seconds' => $durationSeconds,
                ]);
            }
        }

        request()->session()->forget('login_log_id');

        $this->closeProviderOnlineSessions($userId);
    }

    private function closeProviderOnlineSessions(int $userId): void
    {
        $closedAt = now();

        if (Schema::hasTable('provider_online_logs') && Schema::hasColumns('provider_online_logs', ['user_id', 'went_online_at', 'went_offline_at', 'duration_seconds'])) {
            ProviderOnlineLog::query()
                ->where('user_id', $userId)
                ->whereNull('went_offline_at')
                ->get()
                ->each(function (ProviderOnlineLog $log) use ($closedAt): void {
                    $log->update([
                        'went_offline_at' => $closedAt,
                        'duration_seconds' => max(0, (int) $closedAt->diffInSeconds($log->went_online_at)),
                    ]);
                });
        }

        if (Schema::hasTable('online_users') && Schema::hasColumns('online_users', ['user_id', 'status', 'online_started_at', 'online_expires_at'])) {
            OnlineUser::query()
                ->where('user_id', $userId)
                ->where('status', 'online')
                ->update([
                    'status' => 'offline',
                    'online_started_at' => null,
                    'online_expires_at' => null,
                    'updated_at' => $closedAt,
                ]);
        }
    }
}
