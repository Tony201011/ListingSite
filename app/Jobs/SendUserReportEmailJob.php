<?php

namespace App\Jobs;

use App\Models\EmailLog;
use App\Models\SiteSetting;
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

class SendUserReportEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(
        public int $reportId,
    ) {}

    public function handle(MailgunConfigService $mailgunConfig): void
    {
        $report = UserReport::with('providerProfile.user')->find($this->reportId);

        if (! $report) {
            Log::error('User report email job failed: report not found.', [
                'user_report_id' => $this->reportId,
            ]);

            return;
        }

        $setting = $mailgunConfig->applyOrFail(
            'User report email',
            ['user_report_id' => $report->id]
        );

        if (! $setting) {
            return;
        }

        $adminEmail = SiteSetting::query()
            ->whereNotNull('contact_email')
            ->latest('id')
            ->value('contact_email')
            ?: 'support@hotescorts.com.au';

        $providerEmail = $report->providerProfile?->user?->email;
        $providerName = $report->providerProfile?->name ?? 'Provider';

        $subject = 'New Profile Report: '.$providerName;

        // Send to admin
        try {
            Mail::mailer('mailgun')->send(
                'emails.user-report-admin',
                ['report' => $report],
                function ($message) use ($adminEmail, $subject): void {
                    $message->to($adminEmail)->subject($subject);
                }
            );

            Log::info('User report admin email sent successfully', ['user_report_id' => $report->id]);

            EmailLog::create([
                'recipient' => $adminEmail,
                'subject' => $subject,
                'type' => 'user_report',
                'status' => 'sent',
                'sent_at' => now(),
            ]);
        } catch (Throwable $e) {
            Log::error('User report admin email failed', [
                'user_report_id' => $report->id,
                'error' => $e->getMessage(),
            ]);

            EmailLog::create([
                'recipient' => $adminEmail,
                'subject' => $subject,
                'type' => 'user_report',
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            throw $e;
        }

        // Send notification to provider if email exists
        if (filled($providerEmail)) {
            try {
                Mail::mailer('mailgun')->send(
                    'emails.user-report-provider',
                    ['report' => $report],
                    function ($message) use ($providerEmail, $providerName): void {
                        $message->to($providerEmail, $providerName)
                            ->subject('Your profile has been reported');
                    }
                );

                Log::info('User report provider email sent successfully', ['user_report_id' => $report->id]);

                EmailLog::create([
                    'recipient' => $providerEmail,
                    'subject' => 'Your profile has been reported',
                    'type' => 'user_report_provider',
                    'status' => 'sent',
                    'sent_at' => now(),
                ]);
            } catch (Throwable $e) {
                Log::warning('User report provider email failed', [
                    'user_report_id' => $report->id,
                    'error' => $e->getMessage(),
                ]);

                EmailLog::create([
                    'recipient' => $providerEmail,
                    'subject' => 'Your profile has been reported',
                    'type' => 'user_report_provider',
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'sent_at' => now(),
                ]);
            }
        }
    }
}
