<?php

namespace App\Filament\Resources\TwilioSettings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TwilioSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('account_sid')
                    ->searchable(),
                TextColumn::make('api_sid')
                    ->searchable(),
                TextColumn::make('api_secret')
                    ->searchable(),
                TextColumn::make('phone_number')
                    ->searchable(),
                IconColumn::make('dummy_mode_enabled')
                    ->label('Dummy OTP')
                    ->boolean(),
                TextColumn::make('dummy_mobile_number')
                    ->label('Dummy Number')
                    ->searchable(),
                TextColumn::make('dummy_otp')
                    ->label('Dummy OTP Code')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
