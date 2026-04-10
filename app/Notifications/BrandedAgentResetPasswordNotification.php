<?php

namespace App\Notifications;

use App\Models\EmailLog;
use Filament\Auth\Notifications\ResetPassword as FilamentResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

class BrandedAgentResetPasswordNotification extends FilamentResetPasswordNotification
{
    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Reset Your Password')
            ->view('emails.reset-password', [
                'name' => $notifiable->name ?? 'User',
                'email' => $notifiable->getEmailForPasswordReset(),
                'resetUrl' => $this->url,
            ]);
    }

    public function failed(\Throwable $exception): void
    {
        Log::error('Agent password reset notification failed', [
            'error' => $exception->getMessage(),
        ]);
    }
}
