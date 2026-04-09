<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Models\SmtpSetting;
use App\Services\MailgunConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

class SendPasswordResetLinkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $email,
        public int $mailSettingId
    ) {}

    public function handle(MailgunConfigService $mailgunConfig): void
    {
        $mailSetting = SmtpSetting::find($this->mailSettingId);

        if (! $mailSetting) {
            Log::error('Queued password reset link failed: mail setting not found.', [
                'email' => $this->email,
                'mail_setting_id' => $this->mailSettingId,
            ]);

            return;
        }

        $mailgunConfig->apply($mailSetting);

        try {
            $status = Password::sendResetLink([
                'email' => $this->email,
            ]);

            if ($status === Password::RESET_LINK_SENT) {
                Log::info('Password reset link email sent successfully from queue.', [
                    'email' => $this->email,
                    'status' => $status,
                ]);

                EmailLog::create([
                    'recipient' => $this->email,
                    'subject' => 'Reset Password Notification',
                    'type' => 'password_reset_link',
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } else {
                Log::warning('Password reset link email not sent from queue.', [
                    'email' => $this->email,
                    'status' => $status,
                ]);

                EmailLog::create([
                    'recipient' => $this->email,
                    'subject' => 'Reset Password Notification',
                    'type' => 'password_reset_link',
                    'status' => 'failed',
                    'error' => $status,
                    'sent_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Queued password reset email transport error', [
                'email' => $this->email,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $this->email,
                'subject' => 'Reset Password Notification',
                'type' => 'password_reset_link',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);
        }
    }
}
