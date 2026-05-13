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

class SendPhotoVerificationStatusEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $userId,
        public int $mailSettingId,
        public string $status,
        public ?string $adminNote = null
    ) {}

    public function handle(MailgunConfigService $mailgunConfig): void
    {
        $user = User::find($this->userId);
        $setting = SmtpSetting::find($this->mailSettingId);

        if (! $user) {
            Log::error('Photo verification email job failed: user not found', [
                'user_id' => $this->userId,
            ]);

            return;
        }

        if (! $setting) {
            Log::error('Photo verification email job failed: SMTP setting not found', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mail_setting_id' => $this->mailSettingId,
            ]);

            return;
        }

        $mailgunConfig->apply($setting);

        $subject = $this->status === 'approved'
            ? 'Your Photo Verification Has Been Approved'
            : 'Your Photo Verification Has Been Rejected';

        try {
            Mail::mailer('mailgun')->send(
                'emails.photo-verification-status',
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'status' => $this->status,
                    'adminNote' => $this->adminNote,
                    'signinUrl' => url('/signin'),
                ],
                function ($message) use ($user, $subject): void {
                    $message->to($user->email)
                        ->subject($subject);
                }
            );

            EmailLog::create([
                'recipient' => $user->email,
                'subject' => $subject,
                'type' => 'photo_verification_'.$this->status,
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Photo verification status email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'verification_status' => $this->status,
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $user->email,
                'subject' => $subject,
                'type' => 'photo_verification_'.$this->status,
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);
        }
    }
}
