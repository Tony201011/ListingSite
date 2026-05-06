<?php

namespace App\Filament\Resources\Ads;

use App\Filament\Clusters\Pages;
use App\Filament\Resources\Ads\Pages\ManageAds;
use App\Models\Ad;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AdResource extends Resource
{
    protected static ?string $model = Ad::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static ?string $navigationLabel = 'Ads';

    protected static ?string $modelLabel = 'Ad';

    protected static ?string $pluralModelLabel = 'Ads';

    protected static ?string $slug = 'ads';

    protected static ?string $cluster = Pages::class;

    protected static ?int $navigationSort = 11;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->nullable()
                    ->maxLength(255)
                    ->placeholder('Optional ad label'),
                TextInput::make('link_url')
                    ->label('Destination URL')
                    ->url()
                    ->nullable()
                    ->maxLength(2048)
                    ->placeholder('https://example.com'),
                Select::make('position')
                    ->options(self::positionOptions())
                    ->required()
                    ->default('home_top'),
                Select::make('page_keys')
                    ->label('Pages')
                    ->multiple()
                    ->options(self::pageOptions())
                    ->searchable()
                    ->nullable()
                    ->helperText('Leave blank to show on all pages. Select specific pages to restrict visibility.'),
                FileUpload::make('image_path')
                    ->label('Ad Image')
                    ->image()
                    ->disk('public')
                    ->directory('ads')
                    ->visibility('public')
                    ->acceptedFileTypes(['image/png', 'image/jpeg', 'image/webp', 'image/gif'])
                    ->maxSize(4096)
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->minValue(0),
                DateTimePicker::make('starts_at')
                    ->label('Start Date')
                    ->nullable()
                    ->seconds(false)
                    ->helperText('Leave blank to show immediately'),
                DateTimePicker::make('ends_at')
                    ->label('End Date')
                    ->nullable()
                    ->seconds(false)
                    ->helperText('Leave blank to show indefinitely'),
                Toggle::make('open_in_new_tab')
                    ->label('Open link in new tab')
                    ->default(true),
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
                ImageColumn::make('image_path')
                    ->label('Image')
                    ->disk('public')
                    ->height(50)
                    ->toggleable(),
                TextColumn::make('title')
                    ->searchable()
                    ->placeholder('—')
                    ->limit(30),
                TextColumn::make('position')
                    ->label('Position')
                    ->formatStateUsing(fn (string $state): string => self::positionOptions()[$state] ?? $state)
                    ->badge()
                    ->color('info')
                    ->sortable(),
                TextColumn::make('page_keys')
                    ->label('Pages')
                    ->formatStateUsing(function ($state, Ad $record): string {
                        $keys = collect($record->page_keys ?? []);
                        if ($keys->isEmpty()) {
                            return 'All pages';
                        }

                        return $keys->map(fn ($k) => self::pageOptions()[$k] ?? $k)->implode(', ');
                    }),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('Sort')
                    ->sortable(),
                TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime('d M Y')
                    ->placeholder('Immediately')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('ends_at')
                    ->label('Ends')
                    ->dateTime('d M Y')
                    ->placeholder('Never')
                    ->sortable()
                    ->toggleable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->defaultSort('sort_order')
            ->striped()
            ->emptyStateHeading('No ads added yet')
            ->emptyStateDescription('Create ads here to show them on the frontend pages.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAds::route('/'),
        ];
    }

    private static function positionOptions(): array
    {
        return [
            'home_top' => 'Home — Top (above listings)',
            'home_between' => 'Home — Between listings',
            'home_bottom' => 'Home — Bottom (below listings)',
            'profile_top' => 'Profile page — Top',
            'profile_bottom' => 'Profile page — Bottom',
            'all_pages_top' => 'All Pages — Top',
            'all_pages_bottom' => 'All Pages — Bottom',
        ];
    }

    private static function pageOptions(): array
    {
        return [
            'all-pages' => 'All Pages',
            'home' => 'Home',
            'advanced-search' => 'Advanced Search',
            'signin' => 'Sign In',
            'signup' => 'Sign Up',
            'about-us' => 'About Us',
            'blog' => 'Blog',
            'faq' => 'FAQ',
            'contact-us' => 'Contact Us',
            'pricing' => 'Pricing',
            'help' => 'Help',
            'privacy-policy' => 'Privacy Policy',
            'terms-and-conditions' => 'Terms & Conditions',
            'refund-policy' => 'Refund Policy',
            'anti-spam-policy' => 'Anti-Spam Policy',
        ];
    }
}
