<?php

namespace App\Filament\Agent\Resources;

use App\Filament\Agent\Resources\ProviderListingResource\Pages\CreateProviderListing;
use App\Filament\Agent\Resources\ProviderListingResource\Pages\EditProviderListing;
use App\Filament\Agent\Resources\ProviderListingResource\Pages\ListProviderListings;
use App\Models\Category;
use App\Models\ProviderListing;
use App\Models\ProviderProfile;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProviderListingResource extends Resource
{
    protected static ?string $model = ProviderListing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedListBullet;

    protected static ?string $navigationLabel = 'Provider Listings';

    protected static ?string $modelLabel = 'Listing';

    protected static ?string $pluralModelLabel = 'Listings';

    protected static ?string $slug = 'provider-listings';

    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'agent';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Filament::auth()->user()?->role === User::ROLE_ADMIN) {
            $managedProviderIds = ProviderProfile::query()
                ->whereNotNull('agent_id')
                ->pluck('user_id');

            return $query->whereIn('user_id', $managedProviderIds);
        }

        $managedProviderIds = ProviderProfile::query()
            ->where('agent_id', Filament::auth()->id())
            ->pluck('user_id');

        return $query->whereIn('user_id', $managedProviderIds);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('ProviderListingTabs')
                    ->persistTabInQueryString('tab')
                    ->tabs([
                        Tab::make('Overview')
                            ->icon('heroicon-o-list-bullet')
                            ->schema([
                                Section::make('Listing Details')
                                    ->description('Core information for this provider listing.')
                                    ->icon('heroicon-o-identification')
                                    ->schema([
                                        Select::make('user_id')
                                            ->label('Provider')
                                            ->options(function (): array {
                                                $agentId = Filament::auth()->user()?->role === User::ROLE_ADMIN
                                                    ? null
                                                    : Filament::auth()->id();

                                                return ProviderProfile::query()
                                                    ->when($agentId !== null, fn ($q) => $q->where('agent_id', $agentId))
                                                    ->when($agentId === null, fn ($q) => $q->whereNotNull('agent_id'))
                                                    ->with('user')
                                                    ->get()
                                                    ->mapWithKeys(fn (ProviderProfile $profile): array => [
                                                        $profile->user_id => $profile->user?->email ?? $profile->name,
                                                    ])
                                                    ->all();
                                            })
                                            ->searchable()
                                            ->preload()
                                            ->required(),

                                        TextInput::make('title')
                                            ->label('Title')
                                            ->required()
                                            ->maxLength(255),

                                        TextInput::make('age')
                                            ->label('Age')
                                            ->numeric()
                                            ->minValue(18)
                                            ->maxValue(99),

                                        Select::make('category_id')
                                            ->label('Category')
                                            ->options(fn (): array => Category::query()
                                                ->where('is_active', true)
                                                ->orderBy('sort_order')
                                                ->orderBy('name')
                                                ->pluck('name', 'id')
                                                ->all())
                                            ->searchable()
                                            ->preload()
                                            ->nullable(),

                                        Select::make('website_type')
                                            ->label('Website Type')
                                            ->options([
                                                'adult' => 'Adult',
                                                'porn' => 'Porn',
                                            ])
                                            ->default('adult')
                                            ->required()
                                            ->native(false),

                                        TextInput::make('audience_score')
                                            ->label('Audience Score')
                                            ->numeric()
                                            ->minValue(0)
                                            ->maxValue(999.99)
                                            ->default(0),
                                    ])
                                    ->columns(2)
                                    ->collapsible(),

                                Section::make('Listing Status')
                                    ->description('Control visibility and feature flags for this listing.')
                                    ->icon('heroicon-o-bolt')
                                    ->schema([
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
                                    ->columns(3)
                                    ->collapsible(),
                            ]),

                        Tab::make('Media')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Section::make('Thumbnail')
                                    ->description('Upload a thumbnail image for this listing.')
                                    ->icon('heroicon-o-camera')
                                    ->schema([
                                        FileUpload::make('thumbnail')
                                            ->label('Thumbnail')
                                            ->image()
                                            ->imagePreviewHeight('220')
                                            ->panelAspectRatio('2:1')
                                            ->panelLayout('integrated')
                                            ->disk('public')
                                            ->directory('provider-listings')
                                            ->visibility('public')
                                            ->columnSpanFull(),
                                    ]),
                            ]),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider.email')
                    ->label('Provider Email')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('title')
                    ->label('Title')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('age')
                    ->label('Age')
                    ->sortable(),

                TextColumn::make('categoryItem.name')
                    ->label('Category')
                    ->sortable(),

                TextColumn::make('website_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'porn' => 'danger',
                        default => 'info',
                    }),

                TextColumn::make('audience_score')
                    ->label('Audience Score')
                    ->sortable(),

                IconColumn::make('is_live')
                    ->label('Live')
                    ->boolean(),

                IconColumn::make('is_vip')
                    ->label('VIP')
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProviderListings::route('/'),
            'create' => CreateProviderListing::route('/create'),
            'edit' => EditProviderListing::route('/{record}/edit'),
        ];
    }
}
