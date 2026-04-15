<?php

namespace App\Filament\Clusters\Settings\Resources;

use App\Filament\Clusters\Settings;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;

class SiteSettingResource extends Resource
{
    protected static ?string $model = SiteSetting::class;

    protected static ?string $navigationLabel = 'Site Settings';

    protected static ?string $slug = 'site-settings';

    protected static ?string $cluster = Settings::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

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
            Forms\Components\Toggle::make('captcha_enabled')
                ->label('Enable reCAPTCHA')
                ->default(true)
                ->helperText('When enabled, reCAPTCHA is shown on sign up and sign in pages.'),
            Forms\Components\Textarea::make('cookies_text')->label('Cookie Consent Text')->rows(4),
            Forms\Components\Toggle::make('site_password_enabled')->label('Enable Site Password')->helperText('When enabled, visitors must enter the site password to access the site.'),

            Forms\Components\Toggle::make('short_url')->label('Enable Short URLs')->helperText('When enabled, the site will use short URLs for all links.'),

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
            Forms\Components\TextInput::make('max_search_distance')
                ->label('Max Search Distance (km)')
                ->numeric()
                ->minValue(1)
                ->maxValue(20000)
                ->default(500)
                ->helperText('Maximum distance in kilometres users can filter profiles by. Default is 500 km.'),
            Forms\Components\TextInput::make('home_page_records')
                ->label('Home Page Records Per Page')
                ->numeric()
                ->minValue(1)
                ->maxValue(100)
                ->default(12)
                ->helperText('Number of profiles displayed per page on the home page. Default is 12.'),
            Forms\Components\Toggle::make('fatal_error_page_enabled')
                ->label('Enable Fatal Error Maintenance Page')
                ->default(false)
                ->helperText('When enabled, unhandled server errors show a maintenance-style page with your configured message.'),
            Forms\Components\Textarea::make('fatal_error_default_message')
                ->label('Fatal Error Default Message')
                ->rows(3)
                ->maxLength(1000)
                ->helperText('Default message shown on the fatal error page.'),
            Forms\Components\TextInput::make('fatal_error_query_param')
                ->label('Fatal Error Query Parameter')
                ->maxLength(100)
                ->helperText('Optional query key used to override the message on error pages (example: fatal_message).'),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('meta_key')->label('Meta Key'),
                Tables\Columns\IconColumn::make('enable_cookies')->label('Cookies Enabled')->boolean(),
                Tables\Columns\IconColumn::make('captcha_enabled')->label('Captcha')->boolean(),
                Tables\Columns\IconColumn::make('site_password_enabled')->label('Site Password')->boolean(),
                Tables\Columns\IconColumn::make('short_url')->label('Short URL')->boolean(),
                Tables\Columns\TextColumn::make('contact_email')->label('Contact Email'),
                Tables\Columns\TextColumn::make('max_search_distance')->label('Max Distance (km)'),
                Tables\Columns\TextColumn::make('home_page_records')->label('Home Page Records'),
                Tables\Columns\IconColumn::make('fatal_error_page_enabled')->label('Fatal Error Page')->boolean(),
                Tables\Columns\TextColumn::make('fatal_error_query_param')->label('Fatal Query Param'),
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
