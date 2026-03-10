<?php

namespace App\Filament\Resources\TwilioSettings;

use App\Filament\Clusters\Settings;
use App\Models\TwilioSetting;
use Filament\Facades\Filament;
use App\Filament\Resources\TwilioSettings\Pages\ManageTwilioSettings;
use App\Filament\Resources\TwilioSettings\Schemas\TwilioSettingForm;
use App\Filament\Resources\TwilioSettings\Tables\TwilioSettingsTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TwilioSettingResource extends Resource
{
    protected static ?string $model = TwilioSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Twilio Settings';

    protected static ?string $modelLabel = 'Twilio Setting';

    protected static ?string $pluralModelLabel = 'Twilio Settings';

    protected static ?string $slug = 'twilio-settings';

    protected static ?string $cluster = Settings::class;


    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return TwilioSettingForm::configure($schema);
    }

public static function canCreate(): bool
    {
        return TwilioSetting::query()->doesntExist();
    }

    public static function table(Table $table): Table
    {
        return TwilioSettingsTable::configure($table);
    }

    public static function getPages(): array
    {
         return [
            'index' => ManageTwilioSettings::route('/'),
        ];
    }
}
