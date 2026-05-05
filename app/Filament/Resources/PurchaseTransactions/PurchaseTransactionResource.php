<?php

namespace App\Filament\Resources\PurchaseTransactions;

use App\Actions\Subscription\ProcessStripeRefund;
use App\Filament\Resources\PurchaseTransactions\Pages\ListPurchaseTransactions;
use App\Models\PurchaseTransaction;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Actions\Action;
use Filament\Tables\Actions\ViewAction;
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

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Transaction Details')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('id')
                            ->label('Transaction ID'),
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'paid' => 'success',
                                'pending' => 'warning',
                                'failed' => 'danger',
                                'refunded' => 'info',
                                default => 'gray',
                            }),
                        TextEntry::make('credits')
                            ->label('Credits'),
                        TextEntry::make('amount')
                            ->label('Amount')
                            ->money(fn ($record) => $record->currency ?? 'AUD'),
                        TextEntry::make('currency')
                            ->label('Currency'),
                        TextEntry::make('invoice_name')
                            ->label('Invoice Name')
                            ->placeholder('-'),
                        TextEntry::make('paid_at')
                            ->label('Paid At')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('created_at')
                            ->label('Created At')
                            ->dateTime(),
                    ]),
                Section::make('Provider Information')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Provider Name'),
                        TextEntry::make('user.email')
                            ->label('Provider Email'),
                        TextEntry::make('user.mobile')
                            ->label('Provider Mobile')
                            ->placeholder('-'),
                        TextEntry::make('user.providerProfile.name')
                            ->label('Profile Name')
                            ->placeholder('-'),
                    ]),
                Section::make('Stripe Information')
                    ->columns(1)
                    ->schema([
                        TextEntry::make('stripe_session_id')
                            ->label('Stripe Session ID')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('stripe_payment_intent_id')
                            ->label('Payment Intent ID')
                            ->copyable()
                            ->placeholder('-'),
                        TextEntry::make('receipt_url')
                            ->label('Receipt URL')
                            ->url(fn ($record) => $record->receipt_url)
                            ->openUrlInNewTab()
                            ->placeholder('-'),
                    ]),
            ]);
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
            ->recordActions([
                ViewAction::make()
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->modalHeading('Transaction Details'),

                Action::make('view_receipt')
                    ->label('View Receipt')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(fn (PurchaseTransaction $record): ?string => $record->receipt_url ?: null)
                    ->openUrlInNewTab()
                    ->visible(fn (PurchaseTransaction $record): bool => ! empty($record->receipt_url)),

                Action::make('refund')
                    ->label('Refund')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Refund Transaction')
                    ->modalDescription(fn (PurchaseTransaction $record): string => "Refund \${$record->amount} to {$record->user?->name}? This will process a full refund via Stripe and deduct {$record->credits} credit(s) from their account.")
                    ->modalSubmitActionLabel('Yes, Refund')
                    ->visible(fn (PurchaseTransaction $record): bool => $record->status === 'paid')
                    ->action(function (PurchaseTransaction $record, ProcessStripeRefund $processStripeRefund): void {
                        try {
                            $processStripeRefund->execute($record);

                            Notification::make()
                                ->title('Refund processed successfully')
                                ->success()
                                ->send();
                        } catch (\RuntimeException $e) {
                            Notification::make()
                                ->title('Refund failed')
                                ->body($e->getMessage())
                                ->danger()
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Refund failed')
                                ->body('An unexpected error occurred. Please try again or check Stripe directly.')
                                ->danger()
                                ->send();
                        }
                    }),
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
