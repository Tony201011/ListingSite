<?php

namespace App\Filament\Resources\TwilioSettings\Pages;

use App\Filament\Resources\TwilioSettings\TwilioSettingResource;
use App\Models\TwilioSetting;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;

class ManageTwilioSettings extends ManageRecords
{
    protected static string $resource = TwilioSettingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Add Twilio Setting')
                ->createAnother(false)
                ->visible(fn (): bool => TwilioSetting::query()->doesntExist()),
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
