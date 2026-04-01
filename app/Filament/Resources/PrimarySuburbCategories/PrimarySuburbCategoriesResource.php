<?php

namespace App\Filament\Resources\PrimarySuburbCategories;

use App\Filament\Clusters\Categories;
use App\Filament\Resources\PrimarySuburbCategories\Pages\ManageAttributePrimarySuburbCategories;
use App\Models\Postcode;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PrimarySuburbCategoriesResource extends Resource
{
    protected static ?string $model = Postcode::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAdjustmentsHorizontal;

    protected static ?string $navigationLabel = 'Primary Suburb';

    protected static ?string $modelLabel = 'Primary Suburb';

    protected static ?string $pluralModelLabel = 'Primary Suburbs';

    protected static ?string $slug = 'primary-suburb-categories';

    protected static ?string $cluster = Categories::class;

    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->latest('id');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('state')
                    ->label('State')
                    ->required()
                    ->maxLength(255),

                TextInput::make('city_region')
                    ->required()
                    ->maxLength(255),

                TextInput::make('suburb')
                    ->label('Suburb')
                    ->required()
                    ->maxLength(255),

                TextInput::make('postcode')
                    ->label('Postcode')
                    ->required()
                    ->maxLength(20),

                TextInput::make('longitude')
                    ->label('Longitude')
                    ->required()
                    ->maxLength(50),

                TextInput::make('latitude')
                    ->label('Latitude')
                    ->required()
                    ->maxLength(50),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('state')
                    ->label('State')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('city_region')
                    ->label('City Region')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('suburb')
                    ->label('Suburb')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('postcode')
                    ->label('Postcode')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('longitude')
                    ->label('Longitude')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('latitude')
                    ->label('Latitude')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->defaultSort('id', 'desc')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAttributePrimarySuburbCategories::route('/'),
        ];
    }
}
