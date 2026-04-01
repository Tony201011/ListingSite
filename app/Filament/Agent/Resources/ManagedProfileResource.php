<?php

namespace App\Filament\Agent\Resources;

use App\Filament\Agent\Resources\ManagedProfileResource\Pages\CreateManagedProfile;
use App\Filament\Agent\Resources\ManagedProfileResource\Pages\EditManagedProfile;
use App\Filament\Agent\Resources\ManagedProfileResource\Pages\ListManagedProfiles;
use App\Models\City;
use App\Models\Country;
use App\Models\ProviderProfile;
use App\Models\State;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
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

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'agent';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('agent_id', Filament::auth()->id());
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Profile Name')
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

    public static function getPages(): array
    {
        return [
            'index' => ListManagedProfiles::route('/'),
            'create' => CreateManagedProfile::route('/create'),
            'edit' => EditManagedProfile::route('/{record}/edit'),
        ];
    }
}
