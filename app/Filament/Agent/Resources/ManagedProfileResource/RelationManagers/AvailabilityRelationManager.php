<?php

namespace App\Filament\Agent\Resources\ManagedProfileResource\RelationManagers;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Actions\CreateAction;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AvailabilityRelationManager extends RelationManager
{
    protected static string $relationship = 'availabilities';

    protected static ?string $title = 'Availability';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('day')
                    ->label('Day')
                    ->options([
                        'Monday' => 'Monday',
                        'Tuesday' => 'Tuesday',
                        'Wednesday' => 'Wednesday',
                        'Thursday' => 'Thursday',
                        'Friday' => 'Friday',
                        'Saturday' => 'Saturday',
                        'Sunday' => 'Sunday',
                    ])
                    ->required()
                    ->native(false),

                Toggle::make('enabled')
                    ->label('Enabled')
                    ->default(false),

                Toggle::make('all_day')
                    ->label('All Day')
                    ->default(false),

                Toggle::make('till_late')
                    ->label('Till Late')
                    ->default(false),

                Toggle::make('by_appointment')
                    ->label('By Appointment')
                    ->default(false),

                TimePicker::make('from_time')
                    ->label('From Time')
                    ->seconds(false),

                TimePicker::make('to_time')
                    ->label('To Time')
                    ->seconds(false),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day')
                    ->label('Day')
                    ->sortable(),

                IconColumn::make('enabled')
                    ->label('Enabled')
                    ->boolean(),

                TextColumn::make('from_time')
                    ->label('From'),

                TextColumn::make('to_time')
                    ->label('To'),

                IconColumn::make('all_day')
                    ->label('All Day')
                    ->boolean(),

                IconColumn::make('till_late')
                    ->label('Till Late')
                    ->boolean(),

                IconColumn::make('by_appointment')
                    ->label('By Appointment')
                    ->boolean(),
            ])
            ->modifyQueryUsing(fn ($query) => $query->orderByRaw(
                "FIELD(day, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday')"
            ))
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
