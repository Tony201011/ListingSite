<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Models\PurchaseComplaint;
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

class SendPurchaseComplaintEmailJob implements ShouldQueue
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
            Log::error('Purchase complaint email job failed: complaint not found.', [
                'purchase_complaint_id' => $this->complaintId,
            ]);

            return;
        }

        $setting = $mailgunConfig->applyOrFail(
            'Purchase complaint email',
            ['purchase_complaint_id' => $complaint->id]
        );

        if (! $setting) {
            return;
        }

        $adminEmail = SiteSetting::query()
            ->whereNotNull('contact_email')
            ->latest('id')
            ->value('contact_email')
            ?: 'support@hotescorts.com.au';

        $subject = 'New Purchase Complaint: '.$complaint->subject;

        try {
            Mail::mailer('mailgun')->send(
                'emails.purchase-complaint-admin',
                ['complaint' => $complaint],
                function ($message) use ($adminEmail, $subject, $complaint): void {
                    $message->to($adminEmail)->subject($subject);

                    if (filled($complaint->user?->email)) {
                        $message->replyTo($complaint->user->email, $complaint->user->name ?? null);
                    }
                }
            );

            Log::info('Purchase complaint admin email sent successfully', ['purchase_complaint_id' => $complaint->id]);

            EmailLog::create([
                'recipient' => $adminEmail,
                'subject' => $subject,
                'type' => 'purchase_complaint',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('Purchase complaint admin email failed', [
                'purchase_complaint_id' => $complaint->id,
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $adminEmail,
                'subject' => $subject,
                'type' => 'purchase_complaint',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            throw $e;
        }
    }
}
