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

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static ?string $navigationLabel = 'Mail Settings';

    protected static ?string $modelLabel = 'Mail Setting';

    protected static ?string $pluralModelLabel = 'Mail Settings';

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
                Toggle::make('use_mailgun_sandbox')
                    ->label('Use Sandbox Domain')
                    ->helperText('Enable to send emails via sandbox domain. Disable to use live domain.')
                    ->default(true),
                Select::make('mail_mailer')
                    ->options([
                        'mailgun' => 'Mailgun',
                    ])
                    ->required()
                    ->default('mailgun')
                    ->native(false),
                TextInput::make('mailgun_sandbox_domain')
                    ->label('Sandbox Domain')
                    ->placeholder('sandboxxxxx.mailgun.org')
                    ->helperText('Client sandbox domain from Mailgun.')
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state)
                        ? preg_replace('#^https?://#i', '', rtrim(trim($state), '/'))
                        : $state)
                    ->required()
                    ->maxLength(255),
                TextInput::make('mailgun_live_domain')
                    ->label('Live Domain')
                    ->placeholder('mail.hotescort.com.au')
                    ->helperText('Production/live Mailgun domain.')
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state)
                        ? preg_replace('#^https?://#i', '', rtrim(trim($state), '/'))
                        : $state)
                    ->required()
                    ->maxLength(255),
                TextInput::make('mailgun_secret')
                    ->label('Mailgun Secret')
                    ->password()
                    ->revealable()
                    ->helperText('Paste your Mailgun API key here.')
                    ->required()
                    ->maxLength(512),
                TextInput::make('mailgun_endpoint')
                    ->label('Mailgun Endpoint')
                    ->placeholder('api.mailgun.net')
                    ->helperText('If you have Base URL https://api.mailgun.net, enter api.mailgun.net')
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state)
                        ? (parse_url(trim($state), PHP_URL_HOST)
                            ?: preg_replace('#^https?://#i', '', rtrim(trim($state), '/')))
                        : $state)
                    ->default('api.mailgun.net')
                    ->required()
                    ->maxLength(255),
                TextInput::make('mail_from_address')
                    ->label('From Email')
                    ->email()
                    ->required()
                    ->maxLength(255),
                TextInput::make('mail_from_name')
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
                IconColumn::make('use_mailgun_sandbox')
                    ->label('Sandbox')
                    ->boolean(),
                TextColumn::make('mail_mailer')
                    ->label('Mailer')
                    ->badge()
                    ->sortable(),
                TextColumn::make('mailgun_sandbox_domain')
                    ->label('Sandbox Domain'),
                TextColumn::make('mailgun_live_domain')
                    ->label('Live Domain'),
                TextColumn::make('mail_from_address')
                    ->label('From Email'),
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
            ->emptyStateHeading('No mail settings added yet')
            ->emptyStateDescription('Admin can add Mailgun credentials for outgoing emails.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSmtpSettings::route('/'),
        ];
    }
}
