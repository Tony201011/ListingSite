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
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendAgentAccountEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $userId,
        public string $plainPassword,
        public int $mailSettingId
    ) {}

    public function handle(MailgunConfigService $mailgunConfig): void
    {
        $user = User::find($this->userId);
        $setting = SmtpSetting::find($this->mailSettingId);

        if (! $user) {
            Log::error('Agent account email job failed: user not found', [
                'user_id' => $this->userId,
            ]);

            return;
        }

        if (! $setting) {
            Log::error('Agent account email job failed: SMTP setting not found', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mail_setting_id' => $this->mailSettingId,
            ]);

            return;
        }

        if (! $setting->is_enabled) {
            Log::warning('SMTP setting is disabled but used in agent account email job', [
                'user_id' => $user->id,
                'mail_setting_id' => $setting->id,
            ]);
        }

        $mailgunConfig->apply($setting);

        $verificationUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $user->getKey(),
                'hash' => sha1($user->getEmailForVerification()),
            ]
        );

        $this->sendVerificationEmail($user, $verificationUrl);
        $this->sendAccountCreatedEmail($user);
    }

    private function sendVerificationEmail(User $user, string $verificationUrl): void
    {
        try {
            Mail::mailer('mailgun')->send(
                'emails.verify-email',
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'verificationUrl' => $verificationUrl,
                ],
                function ($message) use ($user): void {
                    $message->to($user->email)
                        ->subject('Verify Your Email Address');
                }
            );

            EmailLog::create([
                'recipient' => $user->email,
                'subject' => 'Verify Your Email Address',
                'type' => 'verify_email',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Agent verification email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $user->email,
                'subject' => 'Verify Your Email Address',
                'type' => 'verify_email',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);
        }
    }

    private function sendAccountCreatedEmail(User $user): void
    {
        try {
            Mail::mailer('mailgun')->send(
                'emails.account-created',
                [
                    'name' => $user->name,
                    'email' => $user->email,
                    'signinUrl' => url('/agent'),
                    'temporaryPassword' => $this->plainPassword,
                ],
                function ($message) use ($user): void {
                    $message->to($user->email)
                        ->subject('Your agent account has been created');
                }
            );

            EmailLog::create([
                'recipient' => $user->email,
                'subject' => 'Your agent account has been created',
                'type' => 'account_created',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Agent account created email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $user->email,
                'subject' => 'Your agent account has been created',
                'type' => 'account_created',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);
        }
    }
}
