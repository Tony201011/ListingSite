<?php

namespace App\Filament\Resources\GoogleRecaptchaSetting\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class GoogleRecaptchaSettingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('domain')
                    ->label('Domain')
                    ->required()
                    ->maxLength(255),

                TextInput::make('site_key')
                    ->label('Site Key')
                    ->required()
                    ->maxLength(255),

                TextInput::make('secret_key')
                    ->label('Secret Key')
                    ->password()
                    ->required()
                    ->maxLength(255),

                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ]);
    }
}
