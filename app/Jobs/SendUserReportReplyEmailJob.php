<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Models\UserReport;
use App\Services\MailgunConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class SendUserReportReplyEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $reportId,
    ) {}

    public function handle(MailgunConfigService $mailgunConfig): void
    {
        $report = UserReport::with('providerProfile')->find($this->reportId);

        if (! $report) {
            Log::error('User report reply email job failed: report not found.', [
                'user_report_id' => $this->reportId,
            ]);

            return;
        }

        if (! filled($report->reporter_email)) {
            Log::warning('User report reply email skipped: no reporter email.', [
                'user_report_id' => $report->id,
            ]);

            return;
        }

        $setting = $mailgunConfig->applyOrFail(
            'User report reply email',
            ['user_report_id' => $report->id, 'email' => $report->reporter_email]
        );

        if (! $setting) {
            return;
        }

        $subject = 'Re: Your Report';

        try {
            Mail::mailer('mailgun')->send(
                'emails.user-report-reply',
                ['report' => $report],
                function ($message) use ($report, $subject): void {
                    $message->to($report->reporter_email, $report->reporter_name ?? null)
                        ->subject($subject);
                }
            );

            Log::info('User report reply email sent successfully', [
                'user_report_id' => $report->id,
                'email' => $report->reporter_email,
            ]);

            EmailLog::create([
                'recipient' => $report->reporter_email,
                'subject' => $subject,
                'type' => 'user_report_reply',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('User report reply email failed', [
                'user_report_id' => $report->id,
                'email' => $report->reporter_email,
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $report->reporter_email,
                'subject' => $subject,
                'type' => 'user_report_reply',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            throw $e;
        }
    }
}
