<?php

namespace App\Filament\Clusters\Settings\Resources\GlobalBanners;

use App\Models\AgeAndConsentPolicy;
use App\Models\ContactUsPage;
use App\Models\ContentModerationPolicy;
use App\Filament\Clusters\Settings\Resources\GlobalBanners\Pages\ManageGlobalBanners;
use App\Http\Controllers\Frontend\BlogController;
use App\Http\Controllers\Frontend\FrontendPageController;
use App\Models\FooterWidget;
use App\Models\GlobalBanner;
use App\Models\PrivacyPolicy;
use App\Models\ProhibitedContentPolicy;
use App\Models\RefundPolicy;
use App\Models\ReportAListingPage;
use App\Models\TermCondition;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use Illuminate\Support\Str;

class GlobalBannerResource extends Resource
{
    /**
     * @var array<string, array{label: string, model: class-string<\Illuminate\Database\Eloquent\Model>}>
     */
    private const LEGAL_PAGE_DEFINITIONS = [
        'terms-and-conditions' => ['label' => 'Terms & Conditions', 'model' => TermCondition::class],
        'privacy-policy' => ['label' => 'Privacy Policy', 'model' => PrivacyPolicy::class],
        'refund-policy' => ['label' => 'Refund Policy', 'model' => RefundPolicy::class],
        'contact-us' => ['label' => 'Contact/Support', 'model' => ContactUsPage::class],
        'content-moderation-policy' => ['label' => 'Content Moderation Policy', 'model' => ContentModerationPolicy::class],
        'report-a-listing' => ['label' => 'Report a Listing', 'model' => ReportAListingPage::class],
        'age-and-consent-policy' => ['label' => 'Age and Consent Policy', 'model' => AgeAndConsentPolicy::class],
        'prohibited-content-policy' => ['label' => 'Prohibited Content/Services Policy', 'model' => ProhibitedContentPolicy::class],
    ];

    protected static ?string $model = GlobalBanner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static ?string $navigationLabel = 'Global Banners';

    protected static ?string $modelLabel = 'Global Banner';

    protected static ?string $pluralModelLabel = 'Global Banners';

    protected static ?string $slug = 'global-banners';

    protected static \UnitEnum|string|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 13;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('page_keys')
                    ->label('Pages')
                    ->multiple()
                    ->options(self::getPageOptions())
                    ->searchable()
                    ->required()
                    ->live()
                    ->afterStateHydrated(function ($state, ?GlobalBanner $record, callable $set): void {
                        if (! empty($state)) {
                            return;
                        }

                        if (! empty($record?->page_keys)) {
                            $set('page_keys', $record->page_keys);

                            return;
                        }

                        if (! empty($record?->page_key)) {
                            $set('page_keys', [$record->page_key]);
                        }
                    })
                    ->afterStateUpdated(function ($state, callable $set): void {
                        $selected = collect((array) $state)
                            ->filter()
                            ->values();

                        if ($selected->contains('all-pages')) {
                            $set('page_keys', ['all-pages']);
                        }
                    })
                    ->dehydrateStateUsing(function ($state): array {
                        return collect((array) $state)
                            ->flatMap(function ($value, $key): array {
                                if (! is_int($key)) {
                                    return filled($value) && $value !== false
                                        ? [trim((string) $key)]
                                        : [];
                                }

                                return filled($value)
                                    ? [trim((string) $value)]
                                    : [];
                            })
                            ->filter(fn (string $key): bool => $key !== '')
                            ->unique()
                            ->values()
                            ->all();
                    })
                    ->helperText('Select one or more pages. Choose All Pages (Global) to apply one banner site-wide.'),
                FileUpload::make('banner_image_path')
                    ->label('Banner Image')
                    ->disk('public')
                    ->directory('global-banners')
                    ->image()
                    ->imageEditor()
                    ->visibility('public')
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp'])
                    ->maxSize(4096)
                    ->required(),
                TextInput::make('banner_title')
                    ->label('Banner Title')
                    ->maxLength(255)
                    ->placeholder('hotescorts.com.au'),
                TextInput::make('banner_subtitle')
                    ->label('Banner Subtitle')
                    ->maxLength(255)
                    ->placeholder('REAL WOMEN NEAR YOU'),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('page_keys')
                    ->label('Pages')
                    ->formatStateUsing(function ($state, GlobalBanner $record): string {
                        $keys = collect($record->page_keys ?? [])
                            ->filter()
                            ->values();

                        if ($keys->isEmpty() && filled($record->page_key)) {
                            $keys = collect([$record->page_key]);
                        }

                        return $keys
                            ->map(fn ($key) => self::getPageOptions()[$key] ?? $key)
                            ->implode(', ');
                    })
                    ->searchable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('banner_title')
                    ->label('Title')
                    ->placeholder('Default')
                    ->limit(28),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make()->slideOver(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->emptyStateHeading('No global banners added yet')
            ->emptyStateDescription('Add banner images and map them to selected pages.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageGlobalBanners::route('/'),
        ];
    }

    public static function getPageOptions(): array
    {
        $footerLegalPageOptions = self::legalPageOptions();

        return collect([
            'all-pages' => 'All Pages (Global)',
            'home' => 'Home',
            'signin' => 'Sign In',
            'signup' => 'Sign Up',
            'reset-password' => 'Reset Password',
            'otp-verification' => 'OTP Verification',
            'my-rate' => 'My Rate',
        ])
            ->merge(self::frontendPageOptions($footerLegalPageOptions))
            ->merge($footerLegalPageOptions)
            ->all();
    }

    /**
     * @param  array<string, string>  $footerLegalPageOptions
     * @return array<string, string>
     */
    private static function frontendPageOptions(array $footerLegalPageOptions): array
    {
        /** @var array<string, string> $options */
        $options = collect(RouteFacade::getRoutes()->getRoutesByMethod()['GET'] ?? [])
            ->filter(function (Route $route): bool {
                $action = (string) $route->getActionName();

                return str_starts_with($action, FrontendPageController::class.'@')
                    || str_starts_with($action, BlogController::class.'@');
            })
            ->mapWithKeys(function (Route $route) use ($footerLegalPageOptions): array {
                $uri = trim((string) $route->uri(), '/');

                if ($uri === '' || str_contains($uri, '{') || str_starts_with($uri, 'api/')) {
                    return [];
                }

                $name = (string) $route->getName();
                if ($name !== '' && (str_ends_with($name, '.load-more') || str_ends_with($name, '.submit'))) {
                    return [];
                }

                return [$uri => self::resolvePageLabel($uri, $footerLegalPageOptions)];
            })
            ->sortBy(fn (string $label): string => Str::lower($label))
            ->all();

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private static function legalPageOptions(): array
    {
        return collect(self::managedLegalPageOptions())
            ->merge(self::footerLegalPageOptions())
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private static function managedLegalPageOptions(): array
    {
        /** @var array<string, string> $options */
        $options = collect(self::LEGAL_PAGE_DEFINITIONS)
            ->mapWithKeys(function (array $definition, string $path): array {
                $title = $definition['model']::query()
                    ->where('is_active', true)
                    ->latest('updated_at')
                    ->value('title');

                if (blank($title)) {
                    return [];
                }

                return [$path => trim((string) $title)];
            })
            ->all();

        return $options;
    }

    /**
     * @return array<string, string>
     */
    private static function footerLegalPageOptions(): array
    {
        /** @var array<int, array{label?: mixed, url?: mixed}> $legalLinks */
        $legalLinks = FooterWidget::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first()?->legal_links ?? [];

        /** @var array<string, string> $options */
        $options = collect($legalLinks)
            ->mapWithKeys(function (array $link): array {
                $label = trim((string) ($link['label'] ?? ''));
                $url = trim((string) ($link['url'] ?? ''));

                if ($label === '' || $url === '') {
                    return [];
                }

                $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

                if ($path === '' || str_contains($path, '{')) {
                    return [];
                }

                return [$path => $label];
            })
            ->all();

        return $options;
    }

    /**
     * @param  array<string, string>  $footerLegalPageOptions
     */
    private static function resolvePageLabel(string $key, array $footerLegalPageOptions): string
    {
        $footerLabel = $footerLegalPageOptions[$key] ?? null;

        if (filled($footerLabel)) {
            return $footerLabel;
        }

        $managedLabel = self::LEGAL_PAGE_DEFINITIONS[$key]['label'] ?? null;

        if (filled($managedLabel)) {
            return $managedLabel;
        }

        return Str::of($key)
            ->replace('-', ' ')
            ->title()
            ->toString();
    }
}
