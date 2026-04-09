<?php

namespace App\Filament\Resources\SmtpSettings\Pages;

use App\Filament\Resources\SmtpSettings\SmtpSettingResource;
use App\Filament\Resources\SmtpSettings\Widgets\MailSettingsStats;
use App\Models\EmailLog;
use App\Models\SmtpSetting;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ManageSmtpSettings extends ManageRecords
{
    protected static string $resource = SmtpSettingResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            MailSettingsStats::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Mail Setting')
                ->createAnother(false)
                ->visible(fn (): bool => SmtpSetting::query()->doesntExist()),
            Action::make('statsRange')
                ->label('Stats Range')
                ->icon('heroicon-o-calendar-days')
                ->modalHeading('Select Mail Stats Range')
                ->form([
                    Select::make('range')
                        ->label('Range')
                        ->options([
                            'today' => 'Today',
                            '7d' => 'Last 7 Days',
                            '30d' => 'Last 30 Days',
                            'custom' => 'Custom Date Range',
                        ])
                        ->default((string) session('smtp_stats_range', '30d'))
                        ->required()
                        ->native(false),
                    DatePicker::make('date_from')
                        ->label('From Date')
                        ->default(session('smtp_stats_date_from')),
                    DatePicker::make('date_to')
                        ->label('To Date')
                        ->default(session('smtp_stats_date_to')),
                ])
                ->action(function (array $data): void {
                    if (($data['range'] ?? null) === 'custom') {
                        $from = $data['date_from'] ?? null;
                        $to = $data['date_to'] ?? null;

                        if (! $from || ! $to) {
                            Notification::make()
                                ->title('Please select both From Date and To Date for custom range.')
                                ->danger()
                                ->send();

                            return;
                        }

                        if ($from > $to) {
                            Notification::make()
                                ->title('From Date cannot be after To Date.')
                                ->danger()
                                ->send();

                            return;
                        }

                        session([
                            'smtp_stats_range' => 'custom',
                            'smtp_stats_date_from' => $from,
                            'smtp_stats_date_to' => $to,
                        ]);

                        Notification::make()
                            ->title('Custom mail stats range updated.')
                            ->success()
                            ->send();

                        return;
                    }

                    session(['smtp_stats_range' => $data['range']]);
                    session()->forget(['smtp_stats_date_from', 'smtp_stats_date_to']);

                    Notification::make()
                        ->title('Mail stats range updated.')
                        ->success()
                        ->send();
                }),
            Action::make('testMail')
                ->label('Test Mail')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn (): bool => SmtpSetting::query()->exists())
                ->modalHeading('Send Test Email')
                ->form([
                    TextInput::make('email')
                        ->label('Recipient Email')
                        ->email()
                        ->required(),
                ])
                ->action(function (array $data): void {
                    $setting = SmtpSetting::query()->latest('updated_at')->first();

                    if (! $setting) {
                        Notification::make()
                            ->title('Mail setting not found.')
                            ->danger()
                            ->send();

                        return;
                    }

                    $this->applyMailConfig($setting);

                    try {
                        $mailer = (string) config('mail.default', 'mailgun');

                        Mail::mailer($mailer)->send(
                            'emails.test-mail',
                            [
                                'recipientEmail' => $data['email'],
                                'sentAt' => now()->toDateTimeString(),
                                'appName' => config('app.name', 'HotEscort'),
                            ],
                            function ($message) use ($data): void {
                                $message->to($data['email'])
                                    ->subject('Test Email from Mail Settings');
                            }
                        );

                        Log::info('Test email sent from admin mail settings', [
                            'recipient' => $data['email'],
                            'mailer' => $mailer,
                            'mailgun_domain' => config('services.mailgun.domain'),
                            'mailgun_endpoint' => config('services.mailgun.endpoint'),
                        ]);

                        EmailLog::create([
                            'recipient' => $data['email'],
                            'subject' => 'Test Email from Mail Settings',
                            'type' => 'test_mail',
                            'status' => 'sent',
                            'sent_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Test email sent successfully.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Log::error('Test email failed from admin mail settings', [
                            'recipient' => $data['email'],
                            'mailer_default' => config('mail.default'),
                            'mailgun_domain' => config('services.mailgun.domain'),
                            'mailgun_endpoint' => config('services.mailgun.endpoint'),
                            'error' => $e->getMessage(),
                        ]);

                        EmailLog::create([
                            'recipient' => $data['email'],
                            'subject' => 'Test Email from Mail Settings',
                            'type' => 'test_mail',
                            'status' => 'failed',
                            'error' => $e->getMessage(),
                            'sent_at' => now(),
                        ]);

                        Notification::make()
                            ->title('Failed to send test email.')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    private function applyMailConfig(SmtpSetting $setting): void
    {
        $sandboxDomain = $setting->mailgun_sandbox_domain ?: $setting->mailgun_domain;
        $liveDomain = $setting->mailgun_live_domain;
        $mailgunDomain = $setting->use_mailgun_sandbox
            ? $sandboxDomain
            : ($liveDomain ?: $sandboxDomain);

        $mailgunEndpoint = $setting->mailgun_endpoint ?: 'api.mailgun.net';

        if (filled($mailgunDomain)) {
            $mailgunDomain = preg_replace('#^https?://#i', '', rtrim(trim($mailgunDomain), '/'));
        }

        if (filled($mailgunEndpoint)) {
            $mailgunEndpoint = parse_url(trim($mailgunEndpoint), PHP_URL_HOST)
                ?: preg_replace('#^https?://#i', '', rtrim(trim($mailgunEndpoint), '/'));
        }

        config([
            'mail.default' => $setting->mail_mailer ?: 'mailgun',
            'mail.mailers.mailgun.transport' => 'mailgun',
            'services.mailgun.domain' => $mailgunDomain,
            'services.mailgun.secret' => $setting->mailgun_secret,
            'services.mailgun.endpoint' => $mailgunEndpoint ?: 'api.mailgun.net',
            'mail.from.address' => $setting->mail_from_address ?: config('mail.from.address'),
            'mail.from.name' => $setting->mail_from_name ?: config('mail.from.name'),
        ]);

        app('mail.manager')->forgetMailers();
    }
}
