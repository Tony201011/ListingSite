<?php

namespace App\Filament\Resources\GoogleRecaptchaSetting;

use App\Filament\Clusters\Settings;
use App\Models\GoogleRecaptchaSetting;
use Filament\Facades\Filament;
use App\Filament\Resources\GoogleRecaptchaSetting\Pages\ManageGoogleRecaptchaSettings;
use App\Filament\Resources\GoogleRecaptchaSetting\Schemas\GoogleRecaptchaSettingForm;
use App\Filament\Resources\GoogleRecaptchaSetting\Tables\GoogleRecaptchaSettingsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class GoogleRecaptchaSettingResource extends Resource
{
    protected static ?string $model = GoogleRecaptchaSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Google Recaptcha Settings';

    protected static ?string $modelLabel = 'Google Recaptcha Setting';

    protected static ?string $pluralModelLabel = 'Google Recaptcha Settings';

    protected static ?string $slug = 'google-recaptcha-settings';

    protected static ?string $cluster = Settings::class;


    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return GoogleRecaptchaSettingForm::configure($schema);
    }

public static function canCreate(): bool
    {
        return GoogleRecaptchaSetting::query()->doesntExist();
    }

    public static function table(Table $table): Table
    {
        return GoogleRecaptchaSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
         return [
            'index' => ManageGoogleRecaptchaSettings::route('/'),
        ];
    }
}
