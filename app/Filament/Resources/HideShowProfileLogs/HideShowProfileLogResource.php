<?php

namespace App\Filament\Resources\HideShowProfileLogs;

use App\Filament\Clusters\Logs;
use App\Filament\Resources\HideShowProfileLogs\Pages\ListHideShowProfileLogs;
use App\Models\HideShowProfile;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class HideShowProfileLogResource extends Resource
{
    protected static ?string $model = HideShowProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEye;

    protected static ?string $navigationLabel = 'Show Hide Profile';

    protected static ?string $modelLabel = 'Show Hide Profile';

    protected static ?string $pluralModelLabel = 'Show Hide Profiles';

    protected static ?string $slug = 'show-hide-profile-logs';

    protected static ?string $cluster = Logs::class;

    protected static ?int $navigationSort = 3;

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
                        'show' => 'success',
                        'hide' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
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
                        'show' => 'Show',
                        'hide' => 'Hide',
                    ]),
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->emptyStateHeading('No profile visibility records yet')
            ->emptyStateDescription('Profile show/hide activity will appear here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListHideShowProfileLogs::route('/'),
        ];
    }
}
