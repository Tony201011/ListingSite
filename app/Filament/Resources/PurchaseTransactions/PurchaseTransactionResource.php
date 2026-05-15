<?php

namespace App\Filament\Resources\PurchaseTransactions;

use App\Actions\Subscription\ProcessStripeRefund;
use App\Filament\Resources\PurchaseTransactions\Pages\ListPurchaseTransactions;
use App\Filament\Resources\Users\UserResource;
use App\Models\CreditLog;
use App\Models\ProviderProfile;
use App\Models\PurchaseTransaction;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Facades\Filament;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PurchaseTransactionResource extends Resource
{
    protected static ?string $model = PurchaseTransaction::class;

    private const int WALLET_SPEND_HISTORY_LIMIT = 10;

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

    public static function infolist(Schema $schema): Schema
    {
        return $schema
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
                            ->url(fn ($record) => $record->normalized_receipt_url)
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

                Action::make('wallet_spend_history')
                    ->label('Wallet Summary')
                    ->icon('heroicon-o-eye')
                    ->color('info')
                    ->modalHeading(fn (PurchaseTransaction $record): string => 'Wallet Spend History · '.($record->user?->name ?? 'User'))
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->modalWidth('4xl')
                    ->modalContent(fn (PurchaseTransaction $record) => view('filament.modals.wallet-spend-history', [
                        'summary' => self::getWalletSpendSummary($record),
                        'history' => self::getWalletSpendHistory($record),
                    ])),

                Action::make('view_receipt')
                    ->label('View Receipt')
                    ->icon('heroicon-o-document-text')
                    ->color('info')
                    ->url(fn (PurchaseTransaction $record): ?string => $record->normalized_receipt_url)
                    ->openUrlInNewTab()
                    ->visible(fn (PurchaseTransaction $record): bool => ! empty($record->normalized_receipt_url)),

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

    private static function getWalletSpendHistory(PurchaseTransaction $record): array
    {
        if (! $record->user_id) {
            return [];
        }

        return CreditLog::query()
            ->select([
                'created_at',
                'amount',
                'description',
                'type',
                'reference_type',
                'reference_id',
            ])
            ->where('user_id', $record->user_id)
            ->where('amount', '<', 0)
            ->latest('created_at')
            ->limit(self::WALLET_SPEND_HISTORY_LIMIT)
            ->get()
            ->map(fn (CreditLog $log): array => [
                'spent_at' => $log->created_at,
                'credits_used' => abs($log->amount),
                'description' => $log->description,
                'type' => Str::of($log->type)->replace('_', ' ')->title()->toString(),
                'reference' => $log->reference_type
                    ? class_basename($log->reference_type).($log->reference_id ? " #{$log->reference_id}" : '')
                    : null,
                'details_url' => self::resolveWalletSpendDetailsUrl($log),
            ])
            ->all();
    }

    /**
     * @return array{total_balance: int, used_balance: int, remaining_balance: int}
     */
    private static function getWalletSpendSummary(PurchaseTransaction $record): array
    {
        if (! $record->user_id) {
            return [
                'total_balance' => 0,
                'used_balance' => 0,
                'remaining_balance' => 0,
            ];
        }

        $request = request();
        $cacheKey = (int) $record->user_id;
        $summaryCache = is_object($request)
            ? (array) $request->attributes->get('wallet_spend_summary_cache', [])
            : [];

        if (isset($summaryCache[$cacheKey])) {
            return $summaryCache[$cacheKey];
        }

        $usedBalance = abs((int) CreditLog::query()
            ->where('user_id', $record->user_id)
            ->where('amount', '<', 0)
            ->sum('amount'));

        $remainingBalance = (int) ($record->user?->credits ?? 0);

        $summary = [
            'total_balance' => $usedBalance + $remainingBalance,
            'used_balance' => $usedBalance,
            'remaining_balance' => $remainingBalance,
        ];

        if (is_object($request)) {
            $summaryCache[$cacheKey] = $summary;
            $request->attributes->set('wallet_spend_summary_cache', $summaryCache);
        }

        return $summary;
    }

    private static function resolveWalletSpendDetailsUrl(CreditLog $log): ?string
    {
        if (! $log->reference_type || ! $log->reference_id) {
            return null;
        }

        return match ($log->reference_type) {
            ProviderProfile::class => UserResource::getUrl('edit', ['record' => $log->reference_id]),
            PurchaseTransaction::class => static::getUrl('index', ['tableSearch' => $log->reference_id]),
            default => null,
        };
    }
}
