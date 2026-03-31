<?php

namespace App\Jobs;

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
            } else {
                Log::warning('Password reset link email not sent from queue.', [
                    'email' => $this->email,
                    'status' => $status,
                ]);
            }
        } catch (\Throwable $e) {
            Log::error('Queued password reset email transport error', [
                'email' => $this->email,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);
        }
    }
}
