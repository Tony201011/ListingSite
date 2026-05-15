<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Services\MailgunConfigService;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendFeaturedPurchaseEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $recipientEmail,
        public string $recipientName,
        public string $profileName,
        public string $tierLabel,
        public int $creditCost,
        public int $durationDays,
        public ?string $expiresAt,
        public bool $isExtension = false,
        public ?string $previousExpiry = null,
    ) {}

    public function handle(MailgunConfigService $mailgunConfig): void
    {
        $setting = $mailgunConfig->applyOrFail('SendFeaturedPurchaseEmailJob', [
            'email' => $this->recipientEmail,
            'tier' => $this->tierLabel,
            'is_extension' => $this->isExtension,
        ]);

        if (! $setting) {
            return;
        }

        $subject = $this->isExtension
            ? "{$this->tierLabel} extended successfully"
            : "{$this->tierLabel} activated successfully";

        try {
            Mail::mailer('mailgun')->send(
                'emails.featured-purchase',
                [
                    'name' => $this->recipientName,
                    'email' => $this->recipientEmail,
                    'profileName' => $this->profileName,
                    'tierLabel' => $this->tierLabel,
                    'creditCost' => $this->creditCost,
                    'durationDays' => $this->durationDays,
                    'expiresAt' => $this->formatDate($this->expiresAt),
                    'previousExpiry' => $this->formatDate($this->previousExpiry),
                    'isExtension' => $this->isExtension,
                    'dashboardUrl' => route('featured'),
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
        } catch (Throwable $e) {
            Log::error('Featured purchase email failed', [
                'email' => $this->recipientEmail,
                'tier' => $this->tierLabel,
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

            throw $e;
        }
    }

    private function formatDate(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        return Carbon::parse($value)->format('d M Y');
    }
}
