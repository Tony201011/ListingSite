<?php

namespace App\Filament\Resources\SmtpSettings;

use App\Filament\Clusters\Settings;
use App\Filament\Resources\SmtpSettings\Pages\ManageSmtpSettings;
use App\Models\SmtpSetting;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SmtpSettingResource extends Resource
{
    protected static ?string $model = SmtpSetting::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'SMTP Settings';

    protected static ?string $modelLabel = 'SMTP Setting';

    protected static ?string $pluralModelLabel = 'SMTP Settings';

    protected static ?string $slug = 'smtp-settings';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 10;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return SmtpSetting::query()->doesntExist();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Toggle::make('is_enabled')
                    ->label('Enabled')
                    ->default(false),
                Select::make('mailer')
                    ->options([
                        'smtp' => 'SMTP',
                    ])
                    ->required()
                    ->default('smtp')
                    ->native(false),
                TextInput::make('host')
                    ->label('SMTP Host')
                    ->required()
                    ->maxLength(255),
                TextInput::make('port')
                    ->label('SMTP Port')
                    ->numeric()
                    ->required()
                    ->default(587),
                Select::make('encryption')
                    ->options([
                        'tls' => 'TLS',
                        'ssl' => 'SSL',
                    ])
                    ->native(false)
                    ->nullable(),
                TextInput::make('username')
                    ->label('SMTP Username')
                    ->maxLength(255),
                TextInput::make('password')
                    ->label('SMTP Password')
                    ->password()
                    ->revealable()
                    ->maxLength(255),
                TextInput::make('from_address')
                    ->label('From Email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('from_name')
                    ->label('From Name')
                    ->maxLength(255)
                    ->required(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                IconColumn::make('is_enabled')
                    ->label('Enabled')
                    ->boolean(),
                TextColumn::make('host')
                    ->label('Host')
                    ->searchable(),
                TextColumn::make('port')
                    ->label('Port')
                    ->sortable(),
                TextColumn::make('from_address')
                    ->label('From Email')
                    ->searchable(),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->striped()
            ->emptyStateHeading('No SMTP settings added yet')
            ->emptyStateDescription('Admin can add SMTP credentials for outgoing emails.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSmtpSettings::route('/'),
        ];
    }
}