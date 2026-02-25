<?php

namespace App\Filament\Resources\ProviderListings;

use App\Filament\Resources\ProviderListings\Pages\ManageProviderListings;
use App\Models\Category;
use App\Models\ProviderListing;
use App\Models\User;
use BackedEnum;
use Filament\Facades\Filament;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProviderListingResource extends Resource
{
    protected static ?string $model = ProviderListing::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $navigationLabel = 'Content Listings';

    protected static ?string $modelLabel = 'Listing';

    protected static ?string $pluralModelLabel = 'Listings';

    protected static ?string $slug = 'content-listings';

    protected static ?int $navigationSort = 3;

    public static function canAccess(): bool
    {
        return in_array(Filament::getCurrentPanel()?->getId(), ['admin', 'provider'], true);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['provider', 'categoryItem']);

        if (Filament::getCurrentPanel()?->getId() === 'provider') {
            return $query->where('user_id', Filament::auth()->id());
        }

        return $query;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('provider', 'name', fn (Builder $query): Builder => $query->where('role', User::ROLE_PROVIDER))
                    ->label('Provider')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->visible(fn (): bool => Filament::getCurrentPanel()?->getId() === 'admin'),
                TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                TextInput::make('age')
                    ->numeric()
                    ->minValue(18)
                    ->maxValue(99),
                Select::make('category_id')
                    ->label('Category')
                    ->relationship('categoryItem', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
                    ->required()
                    ->searchable()
                    ->preload(),
                Select::make('website_type')
                    ->label('Website Type')
                    ->options([
                        'adult' => 'Adult',
                        'porn' => 'Porn',
                    ])
                    ->required()
                    ->native(false),
                TextInput::make('audience_score')
                    ->label('Audience Score (%)')
                    ->numeric()
                    ->minValue(0)
                    ->maxValue(100)
                    ->default(0),
                FileUpload::make('thumbnail')
                    ->image()
                    ->disk(fn (): string => config('filesystems.default', 'public'))
                    ->directory('provider-listings')
                    ->visibility('public'),
                Toggle::make('is_live')
                    ->label('Live')
                    ->default(false),
                Toggle::make('is_vip')
                    ->label('VIP')
                    ->default(false),
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
                ImageColumn::make('thumbnail')
                    ->label('')
                    ->disk(fn (): string => config('filesystems.default', 'public'))
                    ->square()
                    ->size(72),
                TextColumn::make('title')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn (ProviderListing $record): string => $record->categoryItem?->name ?? 'No category'),
                TextColumn::make('categoryItem.name')
                    ->label('Category')
                    ->badge()
                    ->searchable(),
                TextColumn::make('provider.name')
                    ->label('Provider')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn (ProviderListing $record): string => $record->provider?->email ?? '-')
                    ->visible(fn (): bool => Filament::getCurrentPanel()?->getId() === 'admin'),
                TextColumn::make('provider.email')
                    ->label('Provider Email')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->searchable()
                    ->visible(fn (): bool => Filament::getCurrentPanel()?->getId() === 'admin'),
                TextColumn::make('website_type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->color(fn (string $state): string => $state === 'porn' ? 'danger' : 'warning'),
                TextColumn::make('audience_score')
                    ->label('Score')
                    ->suffix('%')
                    ->sortable(),
                IconColumn::make('is_live')
                    ->label('Live')
                    ->boolean(),
                IconColumn::make('is_vip')
                    ->label('VIP')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user_id')
                    ->label('Provider')
                    ->relationship('provider', 'name', fn (Builder $query): Builder => $query->where('role', User::ROLE_PROVIDER))
                    ->searchable()
                    ->preload()
                    ->visible(fn (): bool => Filament::getCurrentPanel()?->getId() === 'admin'),
                SelectFilter::make('website_type')
                    ->label('Website Type')
                    ->options([
                        'adult' => 'Adult',
                        'porn' => 'Porn',
                    ]),
                SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('categoryItem', 'name', fn (Builder $query): Builder => $query->where('is_active', true))
                    ->searchable()
                    ->preload(),
                SelectFilter::make('is_live')
                    ->label('Live Status')
                    ->options([
                        '1' => 'Live',
                        '0' => 'Offline',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No listings yet')
            ->emptyStateDescription('Providers can create listings and admin can monitor them here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProviderListings::route('/'),
        ];
    }
}
