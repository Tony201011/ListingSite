<?php

namespace App\Listeners;

use App\Models\EmailLog;
use App\Notifications\BrandedAgentResetPasswordNotification;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;

class LogPasswordResetNotificationEmail
{
    public function handle(NotificationSent|NotificationFailed $event): void
    {
        if (! ($event->notification instanceof BrandedAgentResetPasswordNotification)) {
            return;
        }

        if ($event->channel !== 'mail') {
            return;
        }

        $isFailed = $event instanceof NotificationFailed;

        $exception = $isFailed ? ($event->data['exception'] ?? null) : null;

        EmailLog::create([
            'recipient' => $event->notifiable->email,
            'subject' => 'Reset Your Password',
            'type' => 'password_reset_link',
            'status' => $isFailed ? 'failed' : 'sent',
            'error' => $exception instanceof \Throwable ? $exception->getMessage() : null,
            'sent_at' => now(),
        ]);
    }
}
