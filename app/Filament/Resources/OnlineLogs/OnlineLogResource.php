<?php

namespace App\Filament\Resources\OnlineLogs;

use App\Filament\Clusters\Logs;
use App\Filament\Resources\OnlineLogs\Pages\ListOnlineLogs;
use App\Models\OnlineUser;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OnlineLogResource extends Resource
{
    protected static ?string $model = OnlineUser::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static ?string $navigationLabel = 'Online/Offline Logs';

    protected static ?string $modelLabel = 'Online/Offline Log';

    protected static ?string $pluralModelLabel = 'Online/Offline Logs';

    protected static ?string $slug = 'online-logs';

    protected static ?string $cluster = Logs::class;

    protected static ?int $navigationSort = 5;

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
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'online' => 'success',
                        'offline' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
                TextColumn::make('usage_date')
                    ->label('Usage Date')
                    ->date()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('usage_count')
                    ->label('Usage Count')
                    ->sortable(),
                TextColumn::make('online_started_at')
                    ->label('Online Since')
                    ->dateTime()
                    ->since()
                    ->sortable()
                    ->placeholder('-'),
                TextColumn::make('online_expires_at')
                    ->label('Online Expires At')
                    ->dateTime()
                    ->sortable()
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->since()
                    ->sortable(),
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
                        'online' => 'Online',
                        'offline' => 'Offline',
                    ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->emptyStateHeading('No online/offline records yet')
            ->emptyStateDescription('User online/offline activity will appear here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOnlineLogs::route('/'),
        ];
    }
}
