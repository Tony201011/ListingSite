<?php

namespace App\Jobs;

use App\Models\ContactInquiry;
use App\Models\ContactUsPage;
use App\Models\EmailLog;
use App\Models\SiteSetting;
use App\Services\MailgunConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendContactInquiryEmailJob implements ShouldQueue
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
            Log::error('Contact inquiry email job failed: inquiry not found.', [
                'contact_inquiry_id' => $this->inquiryId,
            ]);

            return;
        }

        $contactPage = ContactUsPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        $recipientEmail = $contactPage?->support_email
            ?: SiteSetting::query()->whereNotNull('contact_email')->latest('id')->value('contact_email')
            ?: 'support@hotescorts.com.au';

        $setting = $mailgunConfig->applyOrFail(
            'Contact inquiry email',
            ['contact_inquiry_id' => $inquiry->id, 'email' => $inquiry->email]
        );

        if (! $setting) {
            return;
        }

        try {
            Mail::mailer('mailgun')->send(
                'emails.contact-inquiry',
                ['inquiry' => $inquiry],
                function ($message) use ($inquiry, $recipientEmail): void {
                    $message->to($recipientEmail)
                        ->subject('New Contact Inquiry: '.($inquiry->subject ?: 'No Subject'));

                    if (filled($inquiry->email)) {
                        $message->replyTo($inquiry->email, $inquiry->name ?? 'Guest');
                    }
                }
            );

            Log::info('Contact inquiry email sent successfully', [
                'contact_inquiry_id' => $inquiry->id,
            ]);

            EmailLog::create([
                'recipient' => $recipientEmail,
                'subject' => 'New Contact Inquiry: '.($inquiry->subject ?: 'No Subject'),
                'type' => 'contact_inquiry',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('Contact inquiry email failed', [
                'contact_inquiry_id' => $inquiry->id,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $recipientEmail,
                'subject' => 'New Contact Inquiry: '.($inquiry->subject ?: 'No Subject'),
                'type' => 'contact_inquiry',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            throw $e;
        }
    }
}
