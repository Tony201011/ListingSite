<?php

namespace App\Filament\Resources\PurchaseTransactions;

use App\Filament\Resources\PurchaseTransactions\Pages\ListPurchaseTransactions;
use App\Models\PurchaseTransaction;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PurchaseTransactionResource extends Resource
{
    protected static ?string $model = PurchaseTransaction::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $navigationLabel = 'Transaction History';

    protected static ?string $modelLabel = 'Transaction';

    protected static ?string $pluralModelLabel = 'Transaction History';

    protected static ?string $slug = 'purchase-transactions';

    protected static ?int $navigationSort = 4;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('Provider Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Provider Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.providerProfile.name')
                    ->label('Profile Name')
                    ->searchable()
                    ->placeholder('-'),
                TextColumn::make('user.mobile')
                    ->label('Provider Mobile')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('credits')
                    ->label('Credits')
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money(fn ($record) => $record->currency ?? 'AUD')
                    ->sortable(),
                TextColumn::make('currency')
                    ->label('Currency')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'pending' => 'warning',
                        'failed' => 'danger',
                        'refunded' => 'info',
                        default => 'gray',
                    })
                    ->sortable(),
                TextColumn::make('invoice_name')
                    ->label('Invoice Name')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('receipt_url')
                    ->label('Receipt')
                    ->url(fn ($record) => $record->receipt_url)
                    ->openUrlInNewTab()
                    ->placeholder('-')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stripe_session_id')
                    ->label('Stripe Session')
                    ->copyable()
                    ->placeholder('-')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('stripe_payment_intent_id')
                    ->label('Payment Intent')
                    ->copyable()
                    ->placeholder('-')
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
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
                    ->options([
                        'paid' => 'Paid',
                        'pending' => 'Pending',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No transactions yet')
            ->emptyStateDescription('Provider purchase transactions will appear here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPurchaseTransactions::route('/'),
        ];
    }
}
