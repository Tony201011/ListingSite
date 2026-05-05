<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Models\PurchaseComplaint;
use App\Services\MailgunConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendPurchaseComplaintReplyEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $complaintId,
    ) {}

    public function handle(MailgunConfigService $mailgunConfig): void
    {
        $complaint = PurchaseComplaint::with(['user', 'purchaseTransaction'])->find($this->complaintId);

        if (! $complaint) {
            Log::error('Purchase complaint reply email job failed: complaint not found.', [
                'purchase_complaint_id' => $this->complaintId,
            ]);

            return;
        }

        $recipientEmail = $complaint->user?->email;

        if (! filled($recipientEmail)) {
            Log::warning('Purchase complaint reply email skipped: no recipient email.', [
                'purchase_complaint_id' => $complaint->id,
            ]);

            return;
        }

        $setting = $mailgunConfig->applyOrFail(
            'Purchase complaint reply email',
            ['purchase_complaint_id' => $complaint->id, 'email' => $recipientEmail]
        );

        if (! $setting) {
            return;
        }

        $subject = 'Re: '.$complaint->subject;

        try {
            Mail::mailer('mailgun')->send(
                'emails.purchase-complaint-reply',
                ['complaint' => $complaint],
                function ($message) use ($recipientEmail, $complaint, $subject): void {
                    $message->to($recipientEmail, $complaint->user?->name ?? null)
                        ->subject($subject);
                }
            );

            Log::info('Purchase complaint reply email sent successfully', [
                'purchase_complaint_id' => $complaint->id,
                'email' => $recipientEmail,
            ]);

            EmailLog::create([
                'recipient' => $recipientEmail,
                'subject' => $subject,
                'type' => 'purchase_complaint_reply',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('Purchase complaint reply email failed', [
                'purchase_complaint_id' => $complaint->id,
                'email' => $recipientEmail,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $recipientEmail,
                'subject' => $subject,
                'type' => 'purchase_complaint_reply',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            throw $e;
        }
    }
}
