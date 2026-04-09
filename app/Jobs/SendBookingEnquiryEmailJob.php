<?php

namespace App\Jobs;

use App\Models\BookingEnquiry;
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

class SendBookingEnquiryEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $enquiryId,
    ) {}

    public function handle(MailgunConfigService $mailgunConfig): void
    {
        $enquiry = BookingEnquiry::with('user')->find($this->enquiryId);

        if (! $enquiry) {
            Log::error('Booking enquiry email job failed: enquiry not found.', [
                'booking_enquiry_id' => $this->enquiryId,
            ]);

            return;
        }

        $recipientEmail = $enquiry->user?->email;

        if (! $recipientEmail) {
            Log::error('Booking enquiry email job failed: provider email not found.', [
                'booking_enquiry_id' => $enquiry->id,
                'user_id' => $enquiry->user_id,
            ]);

            return;
        }

        $setting = $mailgunConfig->applyOrFail(
            'Booking enquiry email',
            ['booking_enquiry_id' => $enquiry->id, 'email' => $enquiry->email]
        );

        if (! $setting) {
            return;
        }

        try {
            Mail::mailer('mailgun')->send(
                'emails.booking-enquiry',
                ['enquiry' => $enquiry],
                function ($message) use ($enquiry, $recipientEmail): void {
                    $message->to($recipientEmail)
                        ->replyTo($enquiry->email, $enquiry->name ?? 'Guest')
                        ->subject('New Booking Enquiry');
                }
            );

            Log::info('Booking enquiry email sent successfully', [
                'booking_enquiry_id' => $enquiry->id,
                'email' => $enquiry->email,
            ]);

            EmailLog::create([
                'recipient' => $recipientEmail,
                'subject' => 'New Booking Enquiry',
                'type' => 'booking_enquiry',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('Booking enquiry email failed', [
                'booking_enquiry_id' => $enquiry->id,
                'email' => $enquiry->email,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $recipientEmail,
                'subject' => 'New Booking Enquiry',
                'type' => 'booking_enquiry',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            throw $e;
        }
    }
}
