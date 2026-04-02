<?php

namespace App\Jobs;

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

class SendProviderAccountEmailsJob implements ShouldQueue
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
        $setting = SmtpSetting::find($this->mailSettingId);

        if (! $user) {
            Log::error('Signup email job failed: user not found', [
                'user_id' => $this->userId,
            ]);

            return;
        }

        if (! $setting) {
            Log::error('Signup email job failed: SMTP setting not found', [
                'user_id' => $user->id,
                'email' => $user->email,
                'mail_setting_id' => $this->mailSettingId,
            ]);

            return;
        }

        if (! $setting->is_enabled) {
            Log::warning('SMTP setting is disabled but used in job', [
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
        } catch (\Throwable $e) {
            Log::error('Verification email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
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
                    'signinUrl' => url('/signin'),
                ],
                function ($message) use ($user): void {
                    $message->to($user->email)
                        ->subject('Your account has been created');
                }
            );
        } catch (\Throwable $e) {
            Log::error('Account created email failed', [
                'user_id' => $user->id,
                'email' => $user->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
