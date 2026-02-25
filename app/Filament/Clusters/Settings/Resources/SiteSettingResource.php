<?php

namespace App\Filament\Clusters\Settings\Resources;

use App\Filament\Clusters\Settings;
use App\Models\SiteSetting;
use Filament\Resources\Resource;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Pages\EditRecord;
use Filament\Resources\Pages\CreateRecord;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms;
use Filament\Tables;
use Filament\Schemas\Schema;
use BackedEnum;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;
    protected static ?string $navigationLabel = 'Site Settings';
    protected static ?string $cluster = Settings::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('meta_key')->label('Meta Key'),
            Forms\Components\Textarea::make('meta_description')->label('Meta Description'),
            Forms\Components\Toggle::make('enable_cookies')->label('Enable Cookie Consent Banner'),
            Forms\Components\Textarea::make('cookies_text')->label('Cookie Consent Text')->rows(4),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('meta_key')->label('Meta Key'),
            Tables\Columns\TextColumn::make('enable_cookies')->label('Cookies Enabled')->boolean(),
            Tables\Columns\TextColumn::make('cookies_text')->label('Cookie Consent Text')->limit(40),
        ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Clusters\Settings\Resources\SiteSettingResource\Pages\ListSiteSettings::route('/'),
            'edit' => \App\Filament\Clusters\Settings\Resources\SiteSettingResource\Pages\EditSiteSetting::route('/{record}/edit'),
            'view' => \App\Filament\Clusters\Settings\Resources\SiteSettingResource\Pages\ViewSiteSetting::route('/{record}'),
            'create' => \App\Filament\Clusters\Settings\Resources\SiteSettingResource\Pages\CreateSiteSetting::route('/create'),
        ];
    }
}
