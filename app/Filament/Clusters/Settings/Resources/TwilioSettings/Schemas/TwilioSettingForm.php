<?php

namespace App\Filament\Clusters\Settings\Resources\TwilioSettings\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class TwilioSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('account_sid')
                    ->default(null),
                TextInput::make('api_sid')
                    ->default(null),
                TextInput::make('api_secret')
                    ->default(null),
                TextInput::make('phone_number')
                    ->tel()
                    ->default(null),
                TextInput::make('otp_expire_time')
                    ->label('OTP Expire Time (minutes)')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(60)
                    ->default(5)
                    ->helperText('How long (in minutes) the OTP code remains valid.')
                    ->required(),
            ]);
    }
}
