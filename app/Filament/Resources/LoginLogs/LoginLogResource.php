<?php

namespace App\Filament\Resources\LoginLogs;

use App\Filament\Clusters\Logs;
use App\Filament\Resources\LoginLogs\Pages\ListLoginLogs;
use App\Models\LoginLog;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LoginLogResource extends Resource
{
    protected static ?string $model = LoginLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowRightOnRectangle;

    protected static ?string $navigationLabel = 'Login Logs';

    protected static ?string $modelLabel = 'Login Log';

    protected static ?string $pluralModelLabel = 'Login Logs';

    protected static ?string $slug = 'login-logs';

    protected static ?string $cluster = Logs::class;

    protected static ?int $navigationSort = 6;

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
                TextColumn::make('user.role')
                    ->label('Role')
                    ->badge()
                    ->sortable(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->copyable()
                    ->placeholder('-'),
                TextColumn::make('user_agent')
                    ->label('User Agent')
                    ->limit(50)
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Login Time')
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('user.role')
                    ->label('Role')
                    ->relationship('user', 'role')
                    ->options([
                        'admin' => 'Admin',
                        'provider' => 'Provider',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No login records yet')
            ->emptyStateDescription('User login activity will appear here once users sign in.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLoginLogs::route('/'),
        ];
    }
}
