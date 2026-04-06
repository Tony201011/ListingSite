<?php

namespace App\Filament\Agent\Resources;

use App\Filament\Agent\Resources\ManagedProfileResource\Pages\CreateManagedProfile;
use App\Filament\Agent\Resources\ManagedProfileResource\Pages\EditManagedProfile;
use App\Filament\Agent\Resources\ManagedProfileResource\Pages\ListManagedProfiles;
use App\Filament\Agent\Resources\ManagedProfileResource\RelationManagers\AvailabilityRelationManager;
use App\Filament\Agent\Resources\ManagedProfileResource\RelationManagers\RatesRelationManager;
use App\Models\City;
use App\Models\Country;
use App\Models\ProviderProfile;
use App\Models\State;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class ManagedProfileResource extends Resource
{
    protected static ?string $model = ProviderProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'My Profiles';

    protected static ?string $modelLabel = 'Profile';

    protected static ?string $pluralModelLabel = 'Profiles';

    protected static ?string $slug = 'managed-profiles';

    protected static ?int $navigationSort = 1;

    protected static function isCreatePage(): bool
    {
        return request()->routeIs('filament.agent.resources.managed-profiles.create');
    }

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'agent';
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();

        if (Filament::auth()->user()?->role === User::ROLE_ADMIN) {
            return $query->whereNotNull('agent_id');
        }

        return $query->where('agent_id', Filament::auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('agent_id')
                    ->label('Agent Account')
                    ->options(fn (): array => User::query()
                        ->where('role', User::ROLE_AGENT)
                        ->where('is_blocked', false)
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->required(fn (): bool => Filament::auth()->user()?->role === User::ROLE_ADMIN)
                    ->visible(fn (): bool => Filament::auth()->user()?->role === User::ROLE_ADMIN),

                TextInput::make('provider_email')
                    ->label('Provider Email')
                    ->email()
                    ->required(fn (): bool => static::isCreatePage())
                    ->maxLength(255)
                    ->unique(User::class, 'email')
                    ->visible(fn (): bool => static::isCreatePage())
                    ->dehydrated(fn (): bool => static::isCreatePage()),

                TextInput::make('provider_password')
                    ->label('Provider Password')
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->required(fn (): bool => static::isCreatePage())
                    ->minLength(8)
                    ->same('provider_password_confirmation')
                    ->visible(fn (): bool => static::isCreatePage())
                    ->dehydrated(fn (): bool => static::isCreatePage()),

                TextInput::make('provider_password_confirmation')
                    ->label('Confirm Provider Password')
                    ->password()
                    ->revealable(filament()->arePasswordsRevealable())
                    ->required(fn (): bool => static::isCreatePage())
                    ->visible(fn (): bool => static::isCreatePage())
                    ->dehydrated(false),

                TextInput::make('name')
                    ->label('Provider Name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function (string $state, callable $set, callable $get): void {
                        if (blank($get('slug'))) {
                            $set('slug', Str::slug($state));
                        }
                    }),

                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ProviderProfile::class, 'slug', ignoreRecord: true)
                    ->helperText('Auto-generated from name. Can be customised.'),

                TextInput::make('age')
                    ->numeric()
                    ->minValue(18)
                    ->maxValue(99),

                TextInput::make('phone')
                    ->tel()
                    ->maxLength(30),

                TextInput::make('whatsapp')
                    ->maxLength(30),

                Textarea::make('description')
                    ->label('Description')
                    ->rows(4)
                    ->columnSpanFull(),

                TextInput::make('introduction_line')
                    ->label('Introduction Line')
                    ->maxLength(255)
                    ->columnSpanFull(),

                Textarea::make('profile_text')
                    ->label('Profile Description')
                    ->rows(5)
                    ->columnSpanFull(),

                Select::make('country_id')
                    ->label('Country')
                    ->options(fn (): array => Country::query()->orderBy('name')->pluck('name', 'id')->all())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(fn ($set) => $set('state_id', null)),

                Select::make('state_id')
                    ->label('State')
                    ->options(fn (Get $get): array => State::query()
                        ->when(filled($get('country_id')), fn ($q) => $q->where('country_id', $get('country_id')))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->live()
                    ->disabled(fn (Get $get): bool => blank($get('country_id')))
                    ->afterStateUpdated(fn ($set) => $set('city_id', null)),

                Select::make('city_id')
                    ->label('City')
                    ->options(fn (Get $get): array => City::query()
                        ->when(filled($get('state_id')), fn ($q) => $q->where('state_id', $get('state_id')))
                        ->orderBy('name')
                        ->pluck('name', 'id')
                        ->all())
                    ->searchable()
                    ->preload()
                    ->disabled(fn (Get $get): bool => blank($get('state_id'))),

                TextInput::make('latitude')
                    ->label('Latitude')
                    ->numeric(),

                TextInput::make('longitude')
                    ->label('Longitude')
                    ->numeric(),

                Toggle::make('is_verified')
                    ->label('Verified')
                    ->default(false),

                Toggle::make('is_featured')
                    ->label('Featured')
                    ->default(false),

                Select::make('profile_status')
                    ->label('Profile Status')
                    ->options([
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->default('pending')
                    ->required()
                    ->native(false),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Profile Name')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.email')
                    ->label('Provider Email')
                    ->searchable(),

                TextColumn::make('agent.name')
                    ->label('Agent')
                    ->toggleable(isToggledHiddenByDefault: false)
                    ->visible(fn (): bool => Filament::auth()->user()?->role === User::ROLE_ADMIN),

                TextColumn::make('phone')
                    ->searchable(),

                TextColumn::make('city.name')
                    ->label('City')
                    ->sortable(),

                TextColumn::make('profile_status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default => 'warning',
                    }),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RatesRelationManager::class,
            AvailabilityRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListManagedProfiles::route('/'),
            'create' => CreateManagedProfile::route('/create'),
            'edit' => EditManagedProfile::route('/{record}/edit'),
        ];
    }
}
