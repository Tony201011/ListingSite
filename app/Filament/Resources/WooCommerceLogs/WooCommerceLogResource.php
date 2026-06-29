<?php

namespace App\Filament\Resources\WooCommerceLogs;

use App\Filament\Resources\WooCommerceLogs\Pages\ListWooCommerceLogs;
use App\Models\CreditPurchase;
use App\Models\SiteSetting;
use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class WooCommerceLogResource extends Resource
{
    protected static ?string $model = CreditPurchase::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static ?string $navigationLabel = 'WooCommerce Logs';

    protected static ?string $modelLabel = 'WooCommerce Log';

    protected static ?string $pluralModelLabel = 'WooCommerce Logs';

    protected static ?string $slug = 'woocommerce-logs';

    protected static UnitEnum|string|null $navigationGroup = 'Logs';

    protected static ?int $navigationSort = 11;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin'
            && SiteSetting::isLoggingEnabled();
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return parent::getEloquentQuery()->whereNotNull('woo_order_id');
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('woo_order_id')
                    ->label('WooCommerce Order ID')
                    ->sortable()
                    ->copyable()
                    ->placeholder('-'),
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('credits')
                    ->label('Credits')
                    ->alignCenter()
                    ->badge()
                    ->color('success')
                    ->sortable(),
                TextColumn::make('amount_cents')
                    ->label('Amount')
                    ->formatStateUsing(fn (int $state, CreditPurchase $record): string => $record->currency . ' $' . number_format($state / 100, 2))
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'refunded' => 'info',
                        'cancelled' => 'danger',
                        'failed' => 'danger',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('paid_at')
                    ->label('Paid At')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'failed' => 'Failed',
                        'cancelled' => 'Cancelled',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No WooCommerce orders yet')
            ->emptyStateDescription('WooCommerce order activity will appear here once orders are processed.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWooCommerceLogs::route('/'),
        ];
    }
}
