<?php

namespace App\Filament\Clusters\Settings\Resources;

use App\Filament\Clusters\Settings;
use App\Filament\Clusters\Settings\Resources\SiteSettingResource\Pages\ManageSiteSettings;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;

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
            TextInput::make('meta_key')->label('Meta Key'),
            Textarea::make('meta_description')->label('Meta Description'),
            Toggle::make('enable_cookies')->label('Enable Cookie Consent Banner'),
            Toggle::make('captcha_enabled')
                ->label('Enable reCAPTCHA')
                ->default(true)
                ->helperText('When enabled, reCAPTCHA is shown on sign up and sign in pages.'),
            Textarea::make('cookies_text')->label('Cookie Consent Text')->rows(4),
            Toggle::make('site_password_enabled')->label('Enable Site Password')->helperText('When enabled, visitors must enter the site password to access the site.'),

            Toggle::make('short_url')->label('Enable Short URLs')->helperText('When enabled, the site will use short URLs for all links.'),

            TextInput::make('site_password')
                ->label('Site Password')
                ->password()
                ->revealable()
                ->dehydrated(fn ($state) => filled($state))
                ->helperText('Set the site password used to grant visitor access. If empty, `SITE_PASSWORD` env will be used.'),
            TextInput::make('contact_email')
                ->label('Contact Email')
                ->email()
                ->maxLength(255)
                ->helperText('Shown on profile contact section for email enquiries.'),
            TextInput::make('max_search_distance')
                ->label('Max Search Distance (km)')
                ->numeric()
                ->minValue(1)
                ->maxValue(20000)
                ->default(500)
                ->helperText('Maximum distance in kilometres users can filter profiles by. Default is 500 km.'),
            Toggle::make('distance_search_enabled')
                ->label('Enable Distance Search')
                ->default(true)
                ->helperText('When enabled, users can filter profiles by distance using their location.'),
            TextInput::make('home_page_records')
                ->label('Home Page Records Per Page')
                ->numeric()
                ->minValue(1)
                ->maxValue(100)
                ->default(12)
                ->helperText('Number of profiles displayed per page on the home page. Default is 12.'),
            Toggle::make('fatal_error_page_enabled')
                ->label('Enable Fatal Error Maintenance Page')
                ->default(false)
                ->helperText('When enabled, unhandled server errors show a maintenance-style page with your configured message.'),
            Textarea::make('fatal_error_default_message')
                ->label('Fatal Error Default Message')
                ->rows(3)
                ->maxLength(1000)
                ->helperText('Default message shown on the fatal error page.'),
            TextInput::make('fatal_error_query_param')
                ->label('Fatal Error Query Parameter')
                ->maxLength(100)
                ->helperText('Optional query key used to override the message on error pages (example: fatal_message).'),
            Toggle::make('logging_enabled')
                ->label('Enable Logs')
                ->default(true)
                ->helperText('When enabled, the Logs section is visible in the admin navigation. When disabled, all log pages are hidden.'),
            TextInput::make('max_video_upload_mb')
                ->label('Max Video Upload Size (MB)')
                ->numeric()
                ->minValue(1)
                ->maxValue(10240)
                ->default(100)
                ->required()
                ->helperText('Maximum video file size users can upload in megabytes. Default is 100 MB.'),

            Section::make('Featured & Ad Tier Pricing')
                ->description('Credit costs and durations for featured listings and sponsored ad placements.')
                ->schema([
                    TextInput::make('free_listing_days')
                        ->label('Free Listing Days (new profiles)')
                        ->numeric()
                        ->minValue(0)
                        ->maxValue(365)
                        ->default(21)
                        ->helperText('Number of free days before the daily listing fee kicks in for new profiles. Default is 21.'),
                    TextInput::make('featured_duration_days')
                        ->label('Ad Purchase Duration (days)')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->helperText('Ad tiers are charged daily. Keep this at 1 day.'),
                    TextInput::make('featured_credit_cost')
                        ->label('Normal Featured – Credits per Purchase')
                        ->numeric()
                        ->minValue(1)
                        ->default(1)
                        ->helperText('Credits charged when a provider activates the Normal Featured badge ($1/day equivalent).'),
                    TextInput::make('home_featured_credit_cost')
                        ->label('Home Page Featured – Credits per Purchase')
                        ->numeric()
                        ->minValue(1)
                        ->default(3)
                        ->helperText('Credits charged for Home Page Featured placement ($3/day equivalent).'),
                    TextInput::make('local_banner_credit_cost')
                        ->label('Local Banner – Credits per Purchase')
                        ->numeric()
                        ->minValue(1)
                        ->default(2)
                        ->helperText('Credits charged for the Local (state) Banner ad placement ($2/day equivalent).'),
                    TextInput::make('home_banner_credit_cost')
                        ->label('Home Banner – Credits per Purchase')
                        ->numeric()
                        ->minValue(1)
                        ->default(5)
                        ->helperText('Credits charged for the national Home Page Banner ad placement ($5/day equivalent).'),
                ]),

            Section::make('Payment Settings')
                ->schema([
                    Select::make('stripe_mode')
                        ->label('Stripe Mode')
                        ->options([
                            'sandbox' => 'Sandbox (Testing)',
                            'live' => 'Live (Production)',
                        ])
                        ->default('sandbox')
                        ->required()
                        ->helperText('Choose Sandbox for testing or Live for production transactions.'),
                    TextInput::make('stripe_publishable_key')
                        ->label('Stripe Publishable Key')
                        ->placeholder('pk_test_... or pk_live_...')
                        ->helperText('Your Stripe publishable key for client-side operations.'),
                    TextInput::make('stripe_secret_key')
                        ->label('Stripe Secret Key')
                        ->password()
                        ->revealable()
                        ->placeholder('sk_test_... or sk_live_...')
                        ->helperText('Your Stripe secret key for server-side operations. Keep this secure.'),
                    TextInput::make('stripe_webhook_secret')
                        ->label('Stripe Webhook Secret')
                        ->password()
                        ->revealable()
                        ->placeholder('whsec_...')
                        ->helperText('Secret for verifying Stripe webhook signatures.'),
                    Toggle::make('stripe_enabled')
                        ->label('Enable Stripe Payments')
                        ->default(false)
                        ->helperText('When enabled, users can make payments using Stripe.'),
                ]),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('meta_key')->label('Meta Key'),
                IconColumn::make('enable_cookies')->label('Cookies Enabled')->boolean(),
                IconColumn::make('captcha_enabled')->label('Captcha')->boolean(),
                IconColumn::make('site_password_enabled')->label('Site Password')->boolean(),
                IconColumn::make('short_url')->label('Short URL')->boolean(),
                TextColumn::make('contact_email')->label('Contact Email'),
                TextColumn::make('max_search_distance')->label('Max Distance (km)'),
                IconColumn::make('distance_search_enabled')->label('Distance Search')->boolean(),
                TextColumn::make('home_page_records')->label('Home Page Records'),
                IconColumn::make('fatal_error_page_enabled')->label('Fatal Error Page')->boolean(),
                TextColumn::make('fatal_error_query_param')->label('Fatal Query Param'),
                IconColumn::make('logging_enabled')->label('Logs Enabled')->boolean(),
                TextColumn::make('max_video_upload_mb')->label('Max Video Upload (MB)'),
                TextColumn::make('cookies_text')->label('Cookie Consent Text')->limit(40),
                IconColumn::make('stripe_enabled')->label('Stripe Enabled')->boolean(),
                TextColumn::make('stripe_publishable_key')->label('Stripe Key')->limit(20),
            ])
            ->recordActions([
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSiteSettings::route('/'),
        ];
    }
}
