<?php

namespace App\Listeners;

use App\Models\LoginLog;
use Illuminate\Auth\Events\Login;

class RecordUserLogin
{
    public function handle(Login $event): void
    {
        LoginLog::create([
            'user_id' => $event->user->getKey(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}
