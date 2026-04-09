<?php

namespace App\Filament\Resources\AvailabilityLogs;

use App\Filament\Clusters\Logs;
use App\Filament\Resources\AvailabilityLogs\Pages\ListAvailabilityLogs;
use App\Models\Availability;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AvailabilityLogResource extends Resource
{
    protected static ?string $model = Availability::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $navigationLabel = 'Availability Log';

    protected static ?string $modelLabel = 'Availability Log';

    protected static ?string $pluralModelLabel = 'Availability Logs';

    protected static ?string $slug = 'availability-logs';

    protected static ?string $cluster = Logs::class;

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
                    ->label('User')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('day')
                    ->label('Day')
                    ->badge()
                    ->sortable(),
                IconColumn::make('enabled')
                    ->label('Enabled')
                    ->boolean()
                    ->sortable(),
                TextColumn::make('from_time')
                    ->label('From')
                    ->placeholder('-'),
                TextColumn::make('to_time')
                    ->label('To')
                    ->placeholder('-'),
                IconColumn::make('all_day')
                    ->label('All Day')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('till_late')
                    ->label('Till Late')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('by_appointment')
                    ->label('By Appointment')
                    ->boolean()
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
                SelectFilter::make('day')
                    ->options([
                        'monday' => 'Monday',
                        'tuesday' => 'Tuesday',
                        'wednesday' => 'Wednesday',
                        'thursday' => 'Thursday',
                        'friday' => 'Friday',
                        'saturday' => 'Saturday',
                        'sunday' => 'Sunday',
                    ]),
                TernaryFilter::make('enabled')
                    ->label('Enabled'),
                TernaryFilter::make('all_day')
                    ->label('All Day'),
                TernaryFilter::make('by_appointment')
                    ->label('By Appointment'),
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->emptyStateHeading('No availability records yet')
            ->emptyStateDescription('Availability settings will appear here once users configure their schedule.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAvailabilityLogs::route('/'),
        ];
    }
}
