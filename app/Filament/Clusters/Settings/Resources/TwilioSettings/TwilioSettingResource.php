<?php

namespace App\Filament\Clusters\Settings\Resources\TwilioSettings;

use App\Filament\Clusters\Settings;
use App\Filament\Clusters\Settings\Resources\TwilioSettings\Pages\CreateTwilioSetting;
use App\Filament\Clusters\Settings\Resources\TwilioSettings\Pages\EditTwilioSetting;
use App\Filament\Clusters\Settings\Resources\TwilioSettings\Pages\ListTwilioSettings;
use App\Filament\Clusters\Settings\Resources\TwilioSettings\Schemas\TwilioSettingForm;
use App\Filament\Clusters\Settings\Resources\TwilioSettings\Tables\TwilioSettingsTable;
use App\Models\TwilioSetting;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class TwilioSettingResource extends Resource
{
    protected static ?string $model = TwilioSetting::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = Settings::class;

    protected static ?string $recordTitleAttribute = 'phone_number';

    public static function form(Schema $schema): Schema
    {
        return TwilioSettingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TwilioSettingsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTwilioSettings::route('/'),
            'create' => CreateTwilioSetting::route('/create'),
            'edit' => EditTwilioSetting::route('/{record}/edit'),
        ];
    }
}
