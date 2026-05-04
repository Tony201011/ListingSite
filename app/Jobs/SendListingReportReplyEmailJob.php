<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Models\ListingReport;
use App\Services\MailgunConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendListingReportReplyEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $reportId,
    ) {}

    public function handle(MailgunConfigService $mailgunConfig): void
    {
        $report = ListingReport::with('providerListing')->find($this->reportId);

        if (! $report) {
            Log::error('Listing report reply email job failed: report not found.', [
                'listing_report_id' => $this->reportId,
            ]);

            return;
        }

        if (! filled($report->reporter_email)) {
            Log::warning('Listing report reply email skipped: no reporter email.', [
                'listing_report_id' => $report->id,
            ]);

            return;
        }

        $setting = $mailgunConfig->applyOrFail(
            'Listing report reply email',
            ['listing_report_id' => $report->id, 'email' => $report->reporter_email]
        );

        if (! $setting) {
            return;
        }

        $subject = 'Re: Your Listing Report';

        try {
            Mail::mailer('mailgun')->send(
                'emails.listing-report-reply',
                ['report' => $report],
                function ($message) use ($report, $subject): void {
                    $message->to($report->reporter_email, $report->reporter_name ?? null)
                        ->subject($subject);
                }
            );

            Log::info('Listing report reply email sent successfully', [
                'listing_report_id' => $report->id,
                'email' => $report->reporter_email,
            ]);

            EmailLog::create([
                'recipient' => $report->reporter_email,
                'subject' => $subject,
                'type' => 'listing_report_reply',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('Listing report reply email failed', [
                'listing_report_id' => $report->id,
                'email' => $report->reporter_email,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $report->reporter_email,
                'subject' => $subject,
                'type' => 'listing_report_reply',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            throw $e;
        }
    }
}
