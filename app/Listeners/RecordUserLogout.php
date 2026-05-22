<?php

namespace App\Listeners;

use App\Models\LoginLog;
use Illuminate\Auth\Events\Logout;

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

        $logId = request()->session()->get('login_log_id');

        $log = $logId
            ? LoginLog::where('id', $logId)->where('user_id', $userId)->whereNull('logged_out_at')->first()
            : LoginLog::where('user_id', $userId)->whereNull('logged_out_at')->latest()->first();

        if (! $log) {
            return;
        }

        $loggedOutAt = now();
        $durationSeconds = max(0, (int) $loggedOutAt->diffInSeconds($log->created_at));

        $log->update([
            'logged_out_at' => $loggedOutAt,
            'duration_seconds' => $durationSeconds,
        ]);

        request()->session()->forget('login_log_id');
    }
}
