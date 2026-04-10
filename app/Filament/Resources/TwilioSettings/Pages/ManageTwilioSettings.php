<?php

namespace App\Filament\Resources\TwilioSettings\Pages;

use App\Filament\Resources\TwilioSettings\TwilioSettingResource;
use App\Filament\Resources\TwilioSettings\Widgets\TwilioAccountStats;
use App\Models\SmsLog;
use App\Models\TwilioSetting;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class ManageTwilioSettings extends ManageRecords
{
    protected static string $resource = TwilioSettingResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            TwilioAccountStats::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Twilio Setting')
                ->createAnother(false)
                ->visible(fn (): bool => TwilioSetting::query()->doesntExist()),
            Action::make('statsRange')
                ->label('Stats Range')
                ->icon('heroicon-o-calendar-days')
                ->modalHeading('Select Twilio Stats Range')
                ->form([
                    Select::make('range')
                        ->label('Range')
                        ->options([
                            'today' => 'Today',
                            '7d' => 'Last 7 Days',
                            '30d' => 'Last 30 Days',
                            'custom' => 'Custom Date Range',
                        ])
                        ->default((string) session('twilio_stats_range', '30d'))
                        ->required()
                        ->native(false),
                    DatePicker::make('date_from')
                        ->label('From Date')
                        ->default(session('twilio_stats_date_from')),
                    DatePicker::make('date_to')
                        ->label('To Date')
                        ->default(session('twilio_stats_date_to')),
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
                            'twilio_stats_range' => 'custom',
                            'twilio_stats_date_from' => $from,
                            'twilio_stats_date_to' => $to,
                        ]);

                        Notification::make()
                            ->title('Custom stats range updated.')
                            ->success()
                            ->send();

                        return;
                    }

                    session(['twilio_stats_range' => $data['range']]);
                    session()->forget(['twilio_stats_date_from', 'twilio_stats_date_to']);

                    Notification::make()
                        ->title('Stats range updated.')
                        ->success()
                        ->send();
                }),
            Action::make('testSms')
                ->label('Test SMS')
                ->icon('heroicon-o-chat-bubble-left-right')
                ->visible(fn (): bool => TwilioSetting::query()->exists())
                ->modalHeading('Send Test SMS')
                ->form([
                    TextInput::make('mobile')
                        ->label('Live Mobile Number')
                        ->placeholder('+61400000000')
                        ->helperText('Enter destination in E.164 format, e.g. +614XXXXXXXX')
                        ->required()
                        ->regex('/^\+\d{8,15}$/'),
                    Textarea::make('message')
                        ->label('Message')
                        ->default('HotEscort test SMS: Twilio settings are working correctly.')
                        ->required()
                        ->maxLength(320)
                        ->rows(4),
                ])
                ->action(function (array $data): void {
                    $setting = TwilioSetting::query()->latest('updated_at')->first();

                    if (! $setting) {
                        Notification::make()
                            ->title('Twilio setting not found.')
                            ->danger()
                            ->send();

                        return;
                    }

                    if (
                        blank($setting->api_sid) ||
                        blank($setting->api_secret) ||
                        blank($setting->account_sid) ||
                        blank($setting->phone_number)
                    ) {
                        Notification::make()
                            ->title('Twilio credentials are incomplete.')
                            ->danger()
                            ->send();

                        return;
                    }

                    try {
                        $client = new Client(
                            $setting->api_sid,
                            $setting->api_secret,
                            $setting->account_sid
                        );

                        $message = $client->messages->create(
                            $data['mobile'],
                            [
                                'from' => $setting->phone_number,
                                'body' => $data['message'],
                            ]
                        );

                        Log::info('Twilio test SMS sent from admin settings', [
                            'to' => $data['mobile'],
                            'from' => $setting->phone_number,
                            'message_preview' => mb_substr((string) $data['message'], 0, 100),
                            'message_sid' => $message->sid ?? null,
                        ]);

                        try {
                            SmsLog::create([
                                'recipient' => $data['mobile'],
                                'message' => $data['message'],
                                'status' => 'sent',
                                'sid' => $message->sid ?? null,
                                'error' => null,
                                'sent_at' => now(),
                            ]);
                        } catch (\Throwable $logException) {
                            report($logException);
                        }

                        Notification::make()
                            ->title('Test SMS sent successfully.')
                            ->success()
                            ->send();
                    } catch (\Throwable $e) {
                        Log::error('Twilio test SMS failed from admin settings', [
                            'to' => $data['mobile'],
                            'from' => $setting->phone_number,
                            'error' => $e->getMessage(),
                            'exception_class' => get_class($e),
                        ]);

                        try {
                            SmsLog::create([
                                'recipient' => $data['mobile'],
                                'message' => $data['message'],
                                'status' => 'failed',
                                'sid' => null,
                                'error' => $e->getMessage(),
                                'sent_at' => now(),
                            ]);
                        } catch (\Throwable $logException) {
                            report($logException);
                        }

                        Notification::make()
                            ->title('Failed to send test SMS.')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
