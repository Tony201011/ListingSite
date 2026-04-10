<?php

namespace App\Notifications;

use Filament\Auth\Notifications\ResetPassword as FilamentResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;

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
}
