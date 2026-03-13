<?php

namespace App\Filament\Resources\GoogleRecaptchaSetting\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class GoogleRecaptchaSettingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('domain')
                    ->label('Domain')
                    ->limit(40),

                TextColumn::make('site_key')
                    ->label('Site Key')
                    ->limit(40),

                TextColumn::make('secret_key')
                    ->label('Secret Key')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->limit(40),

                TextColumn::make('is_active')
                    ->label('Active')
                    ->formatStateUsing(fn ($state): string => $state ? 'Active' : 'Inactive')
                    ->sortable(),

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
