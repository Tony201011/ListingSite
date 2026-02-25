<?php

namespace App\Filament\Resources\SocialLoginSettings;

use App\Filament\Clusters\Settings;
use App\Filament\Resources\SocialLoginSettings\Pages\ManageSocialLoginSettings;
use App\Models\SocialLoginSetting;
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

class SocialLoginSettingResource extends Resource
{
    protected static ?string $model = SocialLoginSetting::class;

    protected static string | BackedEnum | null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'Social Login';

    protected static ?string $modelLabel = 'Social Login Setting';

    protected static ?string $pluralModelLabel = 'Social Login Settings';

    protected static ?string $slug = 'social-login-settings';

    protected static ?string $cluster = Settings::class;

    protected static ?int $navigationSort = 9;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('provider')
                    ->options([
                        SocialLoginSetting::PROVIDER_GOOGLE => 'Google (Gmail)',
                        SocialLoginSetting::PROVIDER_FACEBOOK => 'Facebook',
                        SocialLoginSetting::PROVIDER_TWITTER => 'Twitter (X)',
                    ])
                    ->required()
                    ->native(false)
                    ->unique(ignoreRecord: true),
                Toggle::make('is_enabled')
                    ->label('Enabled')
                    ->default(false),
                TextInput::make('client_id')
                    ->label('Client ID')
                    ->required()
                    ->maxLength(255),
                TextInput::make('client_secret')
                    ->label('Client Secret / Token')
                    ->password()
                    ->revealable()
                    ->required()
                    ->maxLength(255),
                TextInput::make('redirect_url')
                    ->label('Redirect URL')
                    ->url()
                    ->required()
                    ->helperText('Example: ' . url('/auth/google/callback'))
                    ->columnSpanFull(),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('provider')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => ucfirst($state))
                    ->searchable(),
                IconColumn::make('is_enabled')
                    ->label('Enabled')
                    ->boolean(),
                TextColumn::make('redirect_url')
                    ->label('Redirect URL')
                    ->limit(45)
                    ->tooltip(fn (SocialLoginSetting $record): ?string => $record->redirect_url),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()->requiresConfirmation(),
            ])
            ->defaultSort('provider')
            ->striped()
            ->emptyStateHeading('No social login settings added yet')
            ->emptyStateDescription('Admin can configure provider keys and callback URLs from here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSocialLoginSettings::route('/'),
        ];
    }
}