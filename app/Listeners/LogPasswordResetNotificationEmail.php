<?php

namespace App\Listeners;

use App\Models\EmailLog;
use App\Notifications\BrandedAgentResetPasswordNotification;
use Illuminate\Notifications\Events\NotificationSent;

class LogPasswordResetNotificationEmail
{
    public function handle(NotificationSent $event): void
    {
        if (! ($event->notification instanceof BrandedAgentResetPasswordNotification)) {
            return;
        }

        if ($event->channel !== 'mail') {
            return;
        }

        EmailLog::create([
            'recipient' => $event->notifiable->email,
            'subject' => 'Reset Your Password',
            'type' => 'password_reset_link',
            'status' => 'sent',
            'sent_at' => now(),
        ]);
    }
}
