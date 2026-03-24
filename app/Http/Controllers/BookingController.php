<?php

namespace App\Http\Controllers;

use App\Models\BookingEnquiry;
use App\Models\SmtpSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class BookingController extends Controller
{
    public function send(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:50',
            'datetime' => 'nullable|string|max:255',
            'services' => 'nullable|string|max:255',
            'duration' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'message' => 'nullable|string',
        ]);

        $enquiry = BookingEnquiry::create([
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'booking_datetime' => $validated['datetime'] ?? null,
            'services' => $validated['services'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'location' => $validated['location'] ?? null,
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
            'is_read' => false,
        ]);

        $this->sendBookingEnquiryEmail($enquiry);

        return back()->with('success', 'Enquiry sent successfully!');
    }

    private function sendBookingEnquiryEmail(BookingEnquiry $enquiry): void
    {
        $activeMailSetting = SmtpSetting::query()
            ->where('is_enabled', true)
            ->latest('updated_at')
            ->first();

        if (! $activeMailSetting) {
            $activeMailSetting = SmtpSetting::query()
                ->latest('updated_at')
                ->first();
        }

        if (! $activeMailSetting) {
            Log::error('Booking enquiry email failed: no mail setting found.', [
                'booking_enquiry_id' => $enquiry->id,
                'email' => $enquiry->email,
            ]);
            return;
        }

        if (! $activeMailSetting->is_enabled) {
            Log::warning('Booking enquiry email using latest mail setting that is disabled.', [
                'booking_enquiry_id' => $enquiry->id,
                'email' => $enquiry->email,
                'mail_setting_id' => $activeMailSetting->id,
            ]);
        }

        $sandboxDomain = $activeMailSetting->mailgun_sandbox_domain ?: $activeMailSetting->mailgun_domain;
        $liveDomain = $activeMailSetting->mailgun_live_domain;

        $mailgunDomain = $activeMailSetting->use_mailgun_sandbox
            ? $sandboxDomain
            : ($liveDomain ?: $sandboxDomain);

        $mailgunEndpoint = $activeMailSetting->mailgun_endpoint ?: 'api.mailgun.net';

        if (filled($mailgunDomain)) {
            $mailgunDomain = preg_replace('#^https?://#i', '', rtrim(trim($mailgunDomain), '/'));
        }

        if (filled($mailgunEndpoint)) {
            $mailgunEndpoint = parse_url(trim($mailgunEndpoint), PHP_URL_HOST)
                ?: preg_replace('#^https?://#i', '', rtrim(trim($mailgunEndpoint), '/'));
        }

        config([
            'mail.default' => $activeMailSetting->mail_mailer ?: 'mailgun',
            'mail.mailers.mailgun.transport' => 'mailgun',
            'services.mailgun.domain' => $mailgunDomain,
            'services.mailgun.secret' => $activeMailSetting->mailgun_secret,
            'services.mailgun.endpoint' => $mailgunEndpoint ?: 'api.mailgun.net',
            'services.mailgun.scheme' => 'https',
            'mail.from.address' => $activeMailSetting->mail_from_address ?: 'postmaster@' . $mailgunDomain,
            'mail.from.name' => $activeMailSetting->mail_from_name ?: config('app.name'),
        ]);

        app('mail.manager')->forgetMailers();

        Log::info('Booking enquiry email attempt', [
            'booking_enquiry_id' => $enquiry->id,
            'email' => $enquiry->email,
            'mail_setting_id' => $activeMailSetting->id,
            'mail_setting_enabled' => (bool) $activeMailSetting->is_enabled,
            'mailer_used' => 'mailgun',
            'mail_from_address' => config('mail.from.address'),
            'mail_from_name' => config('mail.from.name'),
            'mailgun_domain' => config('services.mailgun.domain'),
            'mailgun_endpoint' => config('services.mailgun.endpoint'),
            'mailgun_secret_present' => filled(config('services.mailgun.secret')),
        ]);

        try {
            Mail::mailer('mailgun')->send(
                'emails.booking-enquiry',
                [
                    'enquiry' => $enquiry,
                ],
                function ($message) use ($enquiry): void {
                    $message->to('your@email.com')
                        ->replyTo($enquiry->email, $enquiry->name ?? 'Guest')
                        ->subject('New Booking Enquiry');
                }
            );

            Log::info('Booking enquiry email sent successfully', [
                'booking_enquiry_id' => $enquiry->id,
                'email' => $enquiry->email,
                'mailer_used' => 'mailgun',
            ]);
        } catch (Throwable $e) {
            Log::error('Booking enquiry email failed', [
                'booking_enquiry_id' => $enquiry->id,
                'email' => $enquiry->email,
                'mailer_used' => 'mailgun',
                'mailgun_domain' => config('services.mailgun.domain'),
                'mailgun_endpoint' => config('services.mailgun.endpoint'),
                'exception_class' => get_class($e),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }
}
