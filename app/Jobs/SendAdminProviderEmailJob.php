<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\MailgunConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendAdminProviderEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $userId,
        public string $emailType,
        public ?string $temporaryPassword = null,
        public ?string $agentName = null,
    ) {}

    public function handle(MailgunConfigService $mailgunConfig): void
    {
        $user = User::find($this->userId);

        if (! $user) {
            Log::error('Admin provider email job failed: user not found.', [
                'user_id' => $this->userId,
                'email_type' => $this->emailType,
            ]);

            return;
        }

        $setting = $mailgunConfig->applyOrFail(
            "Admin provider email ({$this->emailType})",
            ['user_id' => $user->id, 'email' => $user->email]
        );

        if (! $setting) {
            return;
        }

        $config = $this->resolveEmailConfig($user);

        try {
            Mail::mailer('mailgun')->send(
                $config['view'],
                $config['data'],
                function ($message) use ($user, $config): void {
                    $message->to($user->email)
                        ->subject($config['subject']);
                }
            );

            Log::info("Admin provider email ({$this->emailType}) sent successfully", [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);
        } catch (Throwable $e) {
            Log::error("Admin provider email ({$this->emailType}) failed", [
                'user_id' => $user->id,
                'email' => $user->email,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    private function resolveEmailConfig(User $user): array
    {
        return match ($this->emailType) {
            'created' => [
                'view' => 'emails.account-created',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'temporaryPassword' => $this->temporaryPassword,
                    'agentName' => $this->agentName,
                    'signinUrl' => url('/signin'),
                ],
                'subject' => 'Provider Account Created',
            ],
            'blocked' => [
                'view' => 'emails.provider-blocked',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'subject' => 'Provider Account Blocked',
            ],
            'unblocked' => [
                'view' => 'emails.provider-unblocked',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ],
                'subject' => 'Provider Account Reactivated',
            ],
        };
    }
}
