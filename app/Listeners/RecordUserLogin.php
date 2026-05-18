<?php

namespace App\Listeners;

use App\Models\LoginLog;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Str;

class RecordUserLogin
{
    public function handle(Login $event): void
    {
        LoginLog::create([
            'user_id' => $event->user->getKey(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        if (app()->runningInConsole() || ! request()->hasSession()) {
            return;
        }

        request()->session()->flash('auth_session_sync', [
            'id' => (string) Str::uuid(),
            'type' => 'login',
            'timestamp' => now()->getTimestampMs(),
        ]);
    }
}
