<?php

namespace App\Filament\Resources\CreditLogs;

use App\Filament\Resources\CreditLogs\Pages\ListCreditLogs;
use App\Models\CreditLog;
use App\Models\SiteSetting;
use BackedEnum;
use UnitEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CreditLogResource extends Resource
{
    protected static ?string $model = CreditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Credit Logs';

    protected static ?string $modelLabel = 'Credit Log';

    protected static ?string $pluralModelLabel = 'Credit Logs';

    protected static ?string $slug = 'credit-logs';

    protected static UnitEnum|string|null $navigationGroup = 'Logs';

    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin'
            && SiteSetting::isLoggingEnabled();
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
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('amount')
                    ->label('Credits')
                    ->alignCenter()
                    ->formatStateUsing(fn (int $state): string => $state > 0 ? "+{$state}" : (string) $state)
                    ->badge()
                    ->color(fn (int $state): string => $state >= 0 ? 'success' : 'danger')
                    ->sortable(),
                TextColumn::make('type')
                    ->label('Type')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'daily_deduction' => 'Daily Listing Fee',
                        'used' => 'Featured/Ad Spend',
                        'purchase_credit' => 'Credit Purchase',
                        'refund' => 'Refund',
                        'referral_reward' => 'Referral Reward',
                        default => str($state)->headline()->toString(),
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'daily_deduction' => 'warning',
                        'used' => 'danger',
                        'purchase_credit' => 'success',
                        'refund' => 'info',
                        'referral_reward' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        'pending' => 'warning',
                        'reversed' => 'danger',
                        default => 'success',
                    })
                    ->placeholder('-'),
                TextColumn::make('description')
                    ->label('Description')
                    ->searchable()
                    ->limit(80)
                    ->tooltip(fn (CreditLog $record): string => $record->description),
                TextColumn::make('reference_type')
                    ->label('Reference')
                    ->formatStateUsing(fn (?string $state): string => $state ? class_basename($state) : '-')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Logged At')
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('credit_debit')
                    ->label('Credit / Debit')
                    ->placeholder('All')
                    ->trueLabel('Credit')
                    ->falseLabel('Debit')
                    ->queries(
                        true: fn ($query) => $query->where('amount', '>', 0),
                        false: fn ($query) => $query->where('amount', '<', 0),
                        blank: fn ($query) => $query,
                    ),
                SelectFilter::make('type')
                    ->label('Activity')
                    ->searchable()
                    ->options([
                        'daily_deduction' => 'Daily Listing Fee',
                        'used' => 'Featured/Ad Spend',
                        'purchase_credit' => 'Credit Purchase',
                        'refund' => 'Refund',
                        'referral_reward' => 'Referral Reward',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No credit logs yet')
            ->emptyStateDescription('Daily listing charges, featured/ad spends, and credit purchases will appear here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCreditLogs::route('/'),
        ];
    }
}
