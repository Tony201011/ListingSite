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

class SendCreditPurchaseEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public string $recipientEmail,
        public string $recipientName,
        public int $credits,
        public float $amount,
        public string $invoiceName,
    ) {}

    public function handle(MailgunConfigService $mailgunConfig): void
    {
        $setting = $mailgunConfig->applyOrFail('SendCreditPurchaseEmailJob', [
            'email' => $this->recipientEmail,
        ]);

        if (! $setting) {
            return;
        }

        try {
            Mail::mailer('mailgun')->send(
                'emails.credit-purchase',
                [
                    'name' => $this->recipientName,
                    'email' => $this->recipientEmail,
                    'credits' => $this->credits,
                    'amount' => $this->amount,
                    'invoiceName' => $this->invoiceName,
                    'historyUrl' => url('/purchase-history'),
                ],
                function ($message): void {
                    $message->to($this->recipientEmail)
                        ->subject('Your credit purchase was successful');
                }
            );

            EmailLog::create([
                'recipient' => $this->recipientEmail,
                'subject' => 'Your credit purchase was successful',
                'type' => 'credit_purchase',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Credit purchase email failed', [
                'email' => $this->recipientEmail,
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $this->recipientEmail,
                'subject' => 'Your credit purchase was successful',
                'type' => 'credit_purchase',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);
        }
    }
}
