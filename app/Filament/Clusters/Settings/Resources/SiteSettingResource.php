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
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
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
            Tabs::make('SiteSettingsTabs')
                ->tabs([
                    Tab::make('General')
                        ->icon('heroicon-o-home')
                        ->schema([
                            Section::make('Display & Listing')
                                ->compact()
                                ->columns(2)
                                ->schema([
                                    TextInput::make('home_page_records')
                                        ->label('Home Page Records Per Page')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(100)
                                        ->default(12)
                                        ->helperText('Profiles shown per page on the home page. Default: 12.'),
                                    Toggle::make('online_filter_enabled')
                                        ->label('Require Online Status')
                                        ->default(false)
                                        ->helperText('Only show profiles with an active online session.'),
                                    Toggle::make('short_url')
                                        ->label('Enable Short URLs')
                                        ->helperText('Use short URLs for all links on the site.'),
                                ]),
                            Section::make('Cookie Consent')
                                ->compact()
                                ->schema([
                                    Toggle::make('enable_cookies')
                                        ->label('Enable Cookie Consent Banner'),
                                    Textarea::make('cookies_text')
                                        ->label('Cookie Consent Text')
                                        ->rows(3),
                                ]),
                        ]),

                    Tab::make('SEO & Meta')
                        ->icon('heroicon-o-magnifying-glass')
                        ->schema([
                            Section::make('Meta Tags')
                                ->compact()
                                ->schema([
                                    TextInput::make('meta_key')
                                        ->label('Meta Keywords'),
                                    Textarea::make('meta_description')
                                        ->label('Meta Description')
                                        ->rows(3),
                                ]),
                            Section::make('Contact')
                                ->compact()
                                ->schema([
                                    TextInput::make('contact_email')
                                        ->label('Contact Email')
                                        ->email()
                                        ->maxLength(255)
                                        ->helperText('Shown on profile contact section for email enquiries.'),
                                ]),
                        ]),

                    Tab::make('Security')
                        ->icon('heroicon-o-lock-closed')
                        ->schema([
                            Section::make('Site Password')
                                ->compact()
                                ->schema([
                                    Toggle::make('site_password_enabled')
                                        ->label('Enable Site Password')
                                        ->helperText('Visitors must enter the site password to access the site.'),
                                    TextInput::make('site_password')
                                        ->label('Site Password')
                                        ->password()
                                        ->revealable()
                                        ->dehydrated(fn ($state) => filled($state))
                                        ->helperText('If empty, the SITE_PASSWORD environment variable is used.'),
                                ]),
                            Section::make('Anti-Spam')
                                ->compact()
                                ->schema([
                                    Toggle::make('captcha_enabled')
                                        ->label('Enable reCAPTCHA')
                                        ->default(true)
                                        ->helperText('Show reCAPTCHA on sign-up and sign-in pages.'),
                                ]),
                        ]),

                    Tab::make('System')
                        ->icon('heroicon-o-wrench-screwdriver')
                        ->schema([
                            Section::make('Search & Discovery')
                                ->compact()
                                ->columns(2)
                                ->schema([
                                    Toggle::make('distance_search_enabled')
                                        ->label('Enable Distance Search')
                                        ->default(true)
                                        ->helperText('Allow users to filter profiles by location distance.'),
                                    TextInput::make('max_search_distance')
                                        ->label('Max Search Distance (km)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(20000)
                                        ->default(500)
                                        ->helperText('Maximum filterable distance in km. Default: 500.'),
                                ]),
                            Section::make('Uploads & Media')
                                ->compact()
                                ->schema([
                                    TextInput::make('max_video_upload_mb')
                                        ->label('Max Video Upload Size (MB)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->maxValue(10240)
                                        ->default(100)
                                        ->required()
                                        ->helperText('Maximum video file size in megabytes. Default: 100 MB.'),
                                ]),
                            Section::make('Logs & Maintenance')
                                ->compact()
                                ->schema([
                                    Toggle::make('logging_enabled')
                                        ->label('Enable Admin Logs')
                                        ->default(true)
                                        ->helperText('Show the Logs section in the admin navigation.'),
                                    Toggle::make('fatal_error_page_enabled')
                                        ->label('Enable Fatal Error Page')
                                        ->default(false)
                                        ->helperText('Show a maintenance page for unhandled server errors.'),
                                    Textarea::make('fatal_error_default_message')
                                        ->label('Fatal Error Message')
                                        ->rows(2)
                                        ->maxLength(1000)
                                        ->helperText('Default message shown on the error page.'),
                                    TextInput::make('fatal_error_query_param')
                                        ->label('Fatal Error Query Parameter')
                                        ->maxLength(100)
                                        ->helperText('Optional query key to override the error message (e.g. fatal_message).'),
                                ]),
                        ]),

                    Tab::make('Payments & Pricing')
                        ->icon('heroicon-o-credit-card')
                        ->schema([
                            Section::make('Payment Provider')
                                ->compact()
                                ->columns(2)
                                ->schema([
                                    Select::make('default_payment_provider')
                                        ->label('Default Provider')
                                        ->options([
                                            'stripe' => 'Stripe',
                                        ])
                                        ->default('stripe')
                                        ->required()
                                        ->native(false)
                                        ->helperText('Core billing now uses a provider abstraction so gateways can be replaced later.'),
                                    Toggle::make('stripe_enabled')
                                        ->label('Enable Stripe Gateway')
                                        ->default(false)
                                        ->helperText('Keeps Stripe available through the generic payment provider layer.')
                                        ->columnSpanFull(),
                                    Select::make('stripe_mode')
                                        ->label('Stripe Mode')
                                        ->options([
                                            'sandbox' => 'Sandbox (Testing)',
                                            'live' => 'Live (Production)',
                                        ])
                                        ->default('sandbox')
                                        ->required()
                                        ->native(false)
                                        ->helperText('Use Sandbox for testing, Live for production.'),
                                    TextInput::make('stripe_publishable_key')
                                        ->label('Publishable Key')
                                        ->placeholder('pk_test_... or pk_live_...')
                                        ->helperText('Client-side Stripe key.'),
                                    TextInput::make('stripe_secret_key')
                                        ->label('Secret Key')
                                        ->password()
                                        ->revealable()
                                        ->placeholder('sk_test_... or sk_live_...')
                                        ->helperText('Server-side Stripe key. Keep this secure.'),
                                    TextInput::make('stripe_webhook_secret')
                                        ->label('Webhook Secret')
                                        ->password()
                                        ->revealable()
                                        ->placeholder('whsec_...')
                                        ->helperText('Used to verify Stripe webhook signatures.'),
                                ]),
                            Section::make('Ad Tier Pricing')
                                ->description('Daily credit costs for featured listings and sponsored placements.')
                                ->compact()
                                ->columns(2)
                                ->schema([
                                    TextInput::make('free_listing_days')
                                        ->label('Free Listing Days')
                                        ->numeric()
                                        ->minValue(0)
                                        ->maxValue(365)
                                        ->default(21)
                                        ->helperText('Free days before the daily fee applies. Default: 21.'),
                                    TextInput::make('featured_duration_days')
                                        ->label('Ad Duration (days)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1)
                                        ->helperText('Ad tiers are charged daily. Keep at 1.'),
                                    TextInput::make('featured_credit_cost')
                                        ->label('Normal Featured (credits/day)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(1),
                                    TextInput::make('home_featured_credit_cost')
                                        ->label('Home Featured (credits/day)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(3),
                                    TextInput::make('local_banner_credit_cost')
                                        ->label('Local Banner (credits/day)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(2),
                                    TextInput::make('home_banner_credit_cost')
                                        ->label('Home Banner (credits/day)')
                                        ->numeric()
                                        ->minValue(1)
                                        ->default(5),
                                ]),
                            Section::make('Referral Program')
                                ->compact()
                                ->columns(2)
                                ->schema([
                                    Select::make('reward_receiver')
                                        ->label('Reward Receiver')
                                        ->options([
                                            'referrer' => 'Referrer',
                                            'referred' => 'Referred User',
                                            'both' => 'Both',
                                        ])
                                        ->default('referrer')
                                        ->required()
                                        ->native(false),
                                    Select::make('reward_trigger')
                                        ->label('Reward Trigger')
                                        ->options([
                                            'signup' => 'Signup',
                                            'successful_payment' => 'Successful Payment',
                                            'service_completion' => 'Service Completion',
                                        ])
                                        ->default('successful_payment')
                                        ->required()
                                        ->native(false),
                                    Select::make('reward_type')
                                        ->label('Reward Type')
                                        ->options([
                                            'fixed' => 'Fixed',
                                            'percentage' => 'Percentage',
                                        ])
                                        ->default('fixed')
                                        ->required()
                                        ->native(false),
                                    TextInput::make('reward_value')
                                        ->label('Reward Value')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0)
                                        ->required(),
                                    Toggle::make('referred_user_bonus_enabled')
                                        ->label('Enable Referred User Bonus')
                                        ->default(false),
                                    Select::make('referred_user_bonus_type')
                                        ->label('Referred User Bonus Type')
                                        ->options([
                                            'fixed' => 'Fixed',
                                            'percentage' => 'Percentage',
                                        ])
                                        ->default('fixed')
                                        ->native(false),
                                    TextInput::make('referred_user_bonus_value')
                                        ->label('Referred User Bonus Value')
                                        ->numeric()
                                        ->default(0)
                                        ->minValue(0),
                                    Select::make('credit_destination')
                                        ->label('Credit Destination')
                                        ->options([
                                            'wallet' => 'Wallet',
                                        ])
                                        ->default('wallet')
                                        ->required()
                                        ->native(false),
                                ]),
                        ]),
                ])
                ->columnSpanFull(),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('meta_key')
                    ->label('Meta Key')
                    ->limit(30)
                    ->placeholder('—'),
                TextColumn::make('contact_email')
                    ->label('Contact Email')
                    ->placeholder('—'),
                IconColumn::make('enable_cookies')
                    ->label('Cookies')
                    ->boolean(),
                IconColumn::make('captcha_enabled')
                    ->label('reCAPTCHA')
                    ->boolean(),
                IconColumn::make('site_password_enabled')
                    ->label('Site Password')
                    ->boolean(),
                IconColumn::make('stripe_enabled')
                    ->label('Stripe')
                    ->boolean(),
                IconColumn::make('logging_enabled')
                    ->label('Logs')
                    ->boolean(),
                TextColumn::make('home_page_records')
                    ->label('Records/Page'),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
            ])
            ->striped()
            ->emptyStateHeading('No site settings configured')
            ->emptyStateDescription('Click "Add Site Setting" to create the initial configuration.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSiteSettings::route('/'),
        ];
    }
}
