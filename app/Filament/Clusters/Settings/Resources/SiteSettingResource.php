<?php

namespace App\Filament\Clusters\Settings\Resources;

use App\Filament\Clusters\Settings;
use App\Models\SiteSetting;
use Filament\Resources\Resource;
use Filament\Actions\EditAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Facades\Filament;
use Filament\Schemas\Schema;
use BackedEnum;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;
    protected static ?string $navigationLabel = 'Site Settings';
    protected static ?string $slug = 'site-settings';
    protected static ?string $cluster = Settings::class;
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-cog-6-tooth';
    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return SiteSetting::query()->doesntExist();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Forms\Components\TextInput::make('meta_key')->label('Meta Key'),
            Forms\Components\Textarea::make('meta_description')->label('Meta Description'),
            Forms\Components\Toggle::make('enable_cookies')->label('Enable Cookie Consent Banner'),
            Forms\Components\Textarea::make('cookies_text')->label('Cookie Consent Text')->rows(4),
            Forms\Components\Toggle::make('site_password_enabled')->label('Enable Site Password')->helperText('When enabled, visitors must enter the site password to access the site.'),
            Forms\Components\TextInput::make('site_password')
                ->label('Site Password')
                ->password()
                ->revealable()
                ->helperText('Set the site password used to grant visitor access. If empty, `SITE_PASSWORD` env will be used.'),
            Forms\Components\TextInput::make('contact_email')
                ->label('Contact Email')
                ->email()
                ->maxLength(255)
                ->helperText('Shown on profile contact section for email enquiries.'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('meta_key')->label('Meta Key'),
                Tables\Columns\IconColumn::make('enable_cookies')->label('Cookies Enabled')->boolean(),
                Tables\Columns\IconColumn::make('site_password_enabled')->label('Site Password')->boolean(),
                Tables\Columns\TextColumn::make('contact_email')->label('Contact Email'),
                Tables\Columns\TextColumn::make('cookies_text')->label('Cookie Consent Text')->limit(40),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => \App\Filament\Clusters\Settings\Resources\SiteSettingResource\Pages\ManageSiteSettings::route('/'),
        ];
    }
}
