<?php

namespace App\Filament\Resources\CreditPackages;

use App\Filament\Resources\CreditPackages\Pages\ManageCreditPackages;
use App\Models\CreditPackage;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class CreditPackageResource extends Resource
{
    protected static ?string $model = CreditPackage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static ?string $navigationLabel = 'Credit Packages';

    protected static ?string $modelLabel = 'Credit Package';

    protected static ?string $pluralModelLabel = 'Credit Packages';

    protected static ?string $slug = 'credit-packages';

    protected static string|UnitEnum|null $navigationGroup = 'Pages';

    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, callable $get): void {
                        if (blank($get('slug'))) {
                            $set('slug', Str::slug((string) $state));
                        }
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->unique(ignoreRecord: true),
                TextInput::make('credits')
                    ->numeric()
                    ->required()
                    ->minValue(1),
                TextInput::make('bonus_credits')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->minValue(0),
                TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->prefix('AUD $')
                    ->step(0.01),
                Select::make('currency')
                    ->options([
                        'AUD' => 'AUD',
                        'USD' => 'USD',
                        'EUR' => 'EUR',
                    ])
                    ->default('AUD')
                    ->required(),
                TextInput::make('woo_product_id')
                    ->label('Woo Product ID')
                    ->numeric()
                    ->minValue(1)
                    ->nullable(),
                Textarea::make('description')
                    ->nullable()
                    ->maxLength(500)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Active')
                    ->default(true)
                    ->required(),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required()
                    ->minValue(0),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('credits')
                    ->sortable(),
                TextColumn::make('bonus_credits')
                    ->label('Bonus')
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Price')
                    ->money(fn (CreditPackage $record) => $record->currency ?: 'AUD')
                    ->sortable(),
                TextColumn::make('woo_product_id')
                    ->label('Woo Product ID')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('description')
                    ->limit(50)
                    ->toggleable(),
                TextColumn::make('is_active')
                    ->badge()
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Active' : 'Disabled')
                    ->color(fn (bool $state): string => $state ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Sort')
                    ->sortable(),
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
            'index' => ManageCreditPackages::route('/'),
        ];
    }
}
