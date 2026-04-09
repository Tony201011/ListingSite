<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Models\SmtpSetting;
use App\Models\User;
use App\Services\MailgunConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPasswordResetSuccessEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $userId,
        public int $mailSettingId
    ) {}

    public function handle(MailgunConfigService $mailgunConfig): void
    {
        $user = User::find($this->userId);
        $mailSetting = SmtpSetting::find($this->mailSettingId);

        if (! $user || blank($user->email)) {
            Log::warning('Queued password reset success email failed: user missing or email blank.', [
                'user_id' => $this->userId,
            ]);

            return;
        }

        if (! $mailSetting) {
            Log::error('Queued password reset success email failed: mail setting not found.', [
                'user_id' => $this->userId,
                'mail_setting_id' => $this->mailSettingId,
            ]);

            return;
        }

        $mailgunConfig->apply($mailSetting);

        try {
            Mail::send('emails.password-reset-success', [
                'name' => $user->name ?? 'User',
                'email' => $user->email,
                'signinUrl' => route('signin'),
            ], function ($message) use ($user): void {
                $message->to($user->email, $user->name ?? null)
                    ->subject('Your password was changed successfully');
            });

            Log::info('Password reset success email sent from queue', [
                'email' => $user->email,
            ]);

            EmailLog::create([
                'recipient' => $user->email,
                'subject' => 'Your password was changed successfully',
                'type' => 'password_reset_success',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to send password reset success email from queue', [
                'email' => $user->email,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $user->email,
                'subject' => 'Your password was changed successfully',
                'type' => 'password_reset_success',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);
        }
    }
}
