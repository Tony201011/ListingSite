<?php

namespace App\Listeners;

use App\Models\LoginLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Str;

class RecordUserLogin
{
    public function handle(Login $event): void
    {
        $userId = $event->user->getKey();
        $now = now();

        LoginLog::query()
            ->where('user_id', $userId)
            ->whereNull('logged_out_at')
            ->orderBy('created_at')
            ->get()
            ->each(function (LoginLog $openLog) use ($now): void {
                $openLog->update([
                    'logged_out_at' => $now,
                    'duration_seconds' => max(0, (int) $now->diffInSeconds($openLog->created_at)),
                ]);
            });

        $log = LoginLog::create([
            'user_id' => $userId,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        if (! request()->hasSession()) {
            return;
        }

        request()->session()->put('login_log_id', $log->id);

        request()->session()->flash('auth_session_sync', [
            'id' => (string) Str::uuid(),
            'type' => 'login',
            'timestamp' => now()->getTimestampMs(),
        ]);
    }
}
