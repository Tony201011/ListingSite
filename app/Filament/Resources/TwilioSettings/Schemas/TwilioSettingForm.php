<?php

namespace App\Filament\Resources\TwilioSettings\Schemas;

use Filament\Forms\Components\Toggle;
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
                Toggle::make('dummy_mode_enabled')
                    ->label('Enable Dummy Mobile OTP')
                    ->helperText('When enabled, OTP for the dummy number is not sent via Twilio and uses the fixed OTP below.'),
                TextInput::make('dummy_mobile_number')
                    ->label('Dummy Australian Mobile Number')
                    ->placeholder('+61400000000')
                    ->helperText('Must be in +614XXXXXXXX or 04XXXXXXXX format.')
                    ->regex('/^(?:\+614|04)\d{8}$/')
                    ->maxLength(12),
                TextInput::make('dummy_otp')
                    ->label('Dummy OTP')
                    ->placeholder('123456')
                    ->helperText('6-digit OTP used only for the dummy number when dummy mode is enabled.')
                    ->regex('/^\d{6}$/')
                    ->maxLength(6),
            ]);
    }
}
