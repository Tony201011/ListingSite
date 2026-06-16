<?php

namespace App\Jobs;

use App\Models\AccountRestoreRequest;
use App\Models\EmailLog;
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

class SendRestoreAccountEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $userId,
        public string $emailType,
        public ?int $restoreRequestId = null,
    ) {}

    public function handle(MailgunConfigService $mailgunConfig): void
    {
        $user = User::withTrashed()->find($this->userId);

        if (! $user) {
            Log::error('Restore account email job failed: user not found.', [
                'user_id' => $this->userId,
                'email_type' => $this->emailType,
            ]);

            return;
        }

        $setting = $mailgunConfig->applyOrFail(
            "Restore account email ({$this->emailType})",
            ['user_id' => $user->id, 'email' => $user->email]
        );

        if (! $setting) {
            return;
        }

        $restoreRequest = $this->restoreRequestId
            ? AccountRestoreRequest::find($this->restoreRequestId)
            : null;

        $config = $this->resolveEmailConfig($user, $restoreRequest);

        try {
            Mail::mailer('mailgun')->send(
                $config['view'],
                $config['data'],
                function ($message) use ($user, $config): void {
                    $message->to($user->email)
                        ->subject($config['subject']);
                }
            );

            Log::info("Restore account email ({$this->emailType}) sent successfully", [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            EmailLog::create([
                'recipient' => $user->email,
                'subject' => $config['subject'],
                'type' => $this->resolveLogType(),
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error("Restore account email ({$this->emailType}) failed", [
                'user_id' => $user->id,
                'email' => $user->email,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $user->email,
                'subject' => $config['subject'],
                'type' => $this->resolveLogType(),
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            throw $e;
        }
    }

    private function resolveLogType(): string
    {
        return match ($this->emailType) {
            'restore_request_received' => 'restore_request_received',
            'restore_request_replied' => 'restore_request_replied',
            'restore_request_approved' => 'restore_request_approved',
            'restore_request_rejected' => 'restore_request_rejected',
            default => $this->emailType,
        };
    }

    private function resolveEmailConfig(User $user, ?AccountRestoreRequest $restoreRequest): array
    {
        return match ($this->emailType) {
            'restore_request_received' => [
                'view' => 'emails.restore-request-received',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'submittedAt' => $restoreRequest?->created_at,
                ],
                'subject' => 'Account Restoration Request Received',
            ],
            'restore_request_replied' => [
                'view' => 'emails.restore-request-replied',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'adminReply' => $restoreRequest?->admin_reply,
                    'status' => $restoreRequest?->status,
                ],
                'subject' => 'Update on Your Account Restoration Request',
            ],
            'restore_request_approved' => [
                'view' => 'emails.restore-request-approved',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'adminReply' => $restoreRequest?->admin_reply,
                    'signinUrl' => url('/signin'),
                ],
                'subject' => 'Your Account Has Been Restored',
            ],
            'restore_request_rejected' => [
                'view' => 'emails.restore-request-rejected',
                'data' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'adminReply' => $restoreRequest?->admin_reply,
                ],
                'subject' => 'Account Restoration Request Rejected',
            ],
        };
    }
}
