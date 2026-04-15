<?php

namespace App\Jobs;

use App\Models\ContactInquiry;
use App\Models\EmailLog;
use App\Services\MailgunConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendContactInquiryReplyEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $inquiryId,
    ) {}

    public function handle(MailgunConfigService $mailgunConfig): void
    {
        $inquiry = ContactInquiry::find($this->inquiryId);

        if (! $inquiry) {
            Log::error('Contact inquiry reply email job failed: inquiry not found.', [
                'contact_inquiry_id' => $this->inquiryId,
            ]);

            return;
        }

        if (! filled($inquiry->email)) {
            Log::warning('Contact inquiry reply email skipped: no recipient email.', [
                'contact_inquiry_id' => $inquiry->id,
            ]);

            return;
        }

        $setting = $mailgunConfig->applyOrFail(
            'Contact inquiry reply email',
            ['contact_inquiry_id' => $inquiry->id, 'email' => $inquiry->email]
        );

        if (! $setting) {
            return;
        }

        try {
            Mail::mailer('mailgun')->send(
                'emails.contact-inquiry-reply',
                ['inquiry' => $inquiry],
                function ($message) use ($inquiry): void {
                    $message->to($inquiry->email, $inquiry->name ?? null)
                        ->subject('Re: '.($inquiry->subject ?: 'Your Inquiry'));
                }
            );

            Log::info('Contact inquiry reply email sent successfully', [
                'contact_inquiry_id' => $inquiry->id,
                'email' => $inquiry->email,
            ]);

            EmailLog::create([
                'recipient' => $inquiry->email,
                'subject' => 'Re: '.($inquiry->subject ?: 'Your Inquiry'),
                'type' => 'contact_inquiry_reply',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('Contact inquiry reply email failed', [
                'contact_inquiry_id' => $inquiry->id,
                'email' => $inquiry->email,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $inquiry->email,
                'subject' => 'Re: '.($inquiry->subject ?: 'Your Inquiry'),
                'type' => 'contact_inquiry_reply',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            throw $e;
        }
    }
}
