<?php

namespace App\Filament\Resources\RecaptchaLogs;

use App\Filament\Clusters\Logs;
use App\Filament\Resources\RecaptchaLogs\Pages\ListRecaptchaLogs;
use App\Models\RecaptchaLog;
use App\Models\SiteSetting;
use BackedEnum;
use Filament\Facades\Filament;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RecaptchaLogResource extends Resource
{
    protected static ?string $model = RecaptchaLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Recaptcha Logs';

    protected static ?string $modelLabel = 'Recaptcha Log';

    protected static ?string $pluralModelLabel = 'Recaptcha Logs';

    protected static ?string $slug = 'recaptcha-logs';

    protected static ?string $cluster = Logs::class;

    protected static ?int $navigationSort = 7;

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
                TextColumn::make('action')
                    ->label('Action')
                    ->badge()
                    ->sortable()
                    ->searchable(),
                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->placeholder('-')
                    ->copyable()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'success' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    })
                    ->sortable(),
                TextColumn::make('error_codes')
                    ->label('Error Codes')
                    ->placeholder('-')
                    ->formatStateUsing(fn ($state): string => is_array($state) ? implode(', ', $state) : ($state ?? '-'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('hostname')
                    ->label('Hostname')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Logged At')
                    ->dateTime()
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'success' => 'Success',
                        'failed' => 'Failed',
                    ]),
                SelectFilter::make('action')
                    ->options([
                        'signin' => 'Sign In',
                        'signup' => 'Sign Up',
                    ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No reCAPTCHA logs yet')
            ->emptyStateDescription('reCAPTCHA verification activity will appear here once users submit forms.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRecaptchaLogs::route('/'),
        ];
    }
}
