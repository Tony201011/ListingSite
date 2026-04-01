<?php

namespace App\Filament\Resources\PricingPackages;

use App\Filament\Clusters\Pages;
use App\Filament\Resources\PricingPackages\Pages\ManagePricingPackages;
use App\Models\PricingPackage;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PricingPackageResource extends Resource
{
    protected static ?string $model = PricingPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static ?string $navigationLabel = 'Pricing Packages';

    protected static ?string $modelLabel = 'Pricing Package';

    protected static ?string $pluralModelLabel = 'Pricing Packages';

    protected static ?string $slug = 'pricing-packages';

    protected static ?string $cluster = Pages::class;

    protected static ?int $navigationSort = 9;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('pricing_page_id')
                    ->label('Pricing Page')
                    ->relationship('pricingPage', 'title')
                    ->searchable()
                    ->required(),
                TextInput::make('credits')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                TextInput::make('total_price')
                    ->required()
                    ->maxLength(50),
                TextInput::make('price_per_credit')
                    ->required()
                    ->maxLength(50),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->minValue(0),
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
                TextColumn::make('pricingPage.title')
                    ->label('Pricing Page')
                    ->searchable(),
                TextColumn::make('credits')
                    ->sortable(),
                TextColumn::make('total_price')
                    ->label('Total Price')
                    ->sortable(),
                TextColumn::make('price_per_credit')
                    ->label('Price per credit')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Sort')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->defaultSort('sort_order')
            ->striped();
    }

    public static function getPages(): array
    {
        return [
            'index' => ManagePricingPackages::route('/'),
        ];
    }
}
