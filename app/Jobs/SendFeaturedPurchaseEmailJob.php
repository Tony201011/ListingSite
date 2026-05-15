<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Services\MailgunConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendFeaturedPurchaseEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $recipientEmail,
        public string $recipientName,
        public string $tierLabel,
        public int $creditCost,
        public int $durationDays,
        public string $expiresAt,
        public bool $isExtension,
    ) {}

    public function handle(MailgunConfigService $mailgunConfig): void
    {
        $setting = $mailgunConfig->applyOrFail('SendFeaturedPurchaseEmailJob', [
            'email' => $this->recipientEmail,
        ]);

        if (! $setting) {
            return;
        }

        $subject = $this->isExtension
            ? "Your {$this->tierLabel} placement has been extended"
            : "Your {$this->tierLabel} placement is now active";

        try {
            Mail::mailer('mailgun')->send(
                'emails.featured-purchase',
                [
                    'name' => $this->recipientName,
                    'tierLabel' => $this->tierLabel,
                    'creditCost' => $this->creditCost,
                    'durationDays' => $this->durationDays,
                    'expiresAt' => $this->expiresAt,
                    'isExtension' => $this->isExtension,
                    'featuredUrl' => url('/featured-listing'),
                ],
                function ($message) use ($subject): void {
                    $message->to($this->recipientEmail)
                        ->subject($subject);
                }
            );

            EmailLog::create([
                'recipient' => $this->recipientEmail,
                'subject' => $subject,
                'type' => 'featured_purchase',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Featured purchase email failed', [
                'email' => $this->recipientEmail,
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $this->recipientEmail,
                'subject' => $subject,
                'type' => 'featured_purchase',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);
        }
    }
}
