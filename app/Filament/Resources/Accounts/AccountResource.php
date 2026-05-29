<?php

namespace App\Filament\Resources\Accounts;

use App\Filament\Resources\Accounts\Pages\CreateAccount;
use App\Filament\Resources\Accounts\Pages\EditAccount;
use App\Filament\Resources\Accounts\Pages\ListAccounts;
use App\Filament\Resources\Users\UserResource;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AccountResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Accounts';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static string|UnitEnum|null $navigationGroup = 'Account Management';

    protected static ?string $modelLabel = 'Account';

    protected static ?string $pluralModelLabel = 'Accounts';

    protected static ?string $slug = 'account-management/accounts';

    protected static ?int $navigationSort = 1;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('role', User::ROLE_PROVIDER)
            ->withCount('providerProfiles');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Account Details')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(255),
                    TextInput::make('email')
                        ->email()
                        ->required()
                        ->maxLength(255)
                        ->unique(ignoreRecord: true),
                    TextInput::make('mobile')
                        ->maxLength(20),
                    TextInput::make('password')
                        ->password()
                        ->revealable()
                        ->visible(fn (string $operation): bool => $operation === 'create')
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(fn ($state): bool => filled($state))
                        ->minLength(8)
                        ->maxLength(255),
                    TextInput::make('password_confirmation')
                        ->password()
                        ->revealable()
                        ->visible(fn (string $operation): bool => $operation === 'create')
                        ->required(fn (string $operation): bool => $operation === 'create')
                        ->dehydrated(false)
                        ->same('password'),
                    Select::make('account_status')
                        ->options([
                            'active' => 'Active',
                            'inactive' => 'Inactive',
                            'soft_deleted' => 'Soft Deleted',
                            'anonymized' => 'Anonymized',
                        ])
                        ->default('active')
                        ->required(),
                    Toggle::make('is_blocked')
                        ->label('Account Deactivated')
                        ->default(false),
                    Toggle::make('mobile_verified')
                        ->default(false),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('mobile')->searchable()->toggleable(),
                TextColumn::make('account_status')
                    ->badge()
                    ->sortable(),
                IconColumn::make('is_blocked')
                    ->label('Deactivated')
                    ->boolean(),
                TextColumn::make('provider_profiles_count')
                    ->label('Profile Count')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('account_status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'soft_deleted' => 'Soft Deleted',
                        'anonymized' => 'Anonymized',
                    ]),
                SelectFilter::make('is_blocked')
                    ->label('Account Deactivated')
                    ->options([
                        '1' => 'Yes',
                        '0' => 'No',
                    ]),
            ])
            ->recordActions([
                ActionGroup::make([
                    EditAction::make(),
                    DeleteAction::make(),
                    Action::make('manageProfiles')
                        ->label('Manage Profiles')
                        ->icon('heroicon-o-identification')
                        ->url(fn (User $record): string => UserResource::getUrl('index', ['account_id' => $record->id])),
                    Action::make('toggleActive')
                        ->label(fn (User $record): string => $record->is_blocked ? 'Activate' : 'Deactivate')
                        ->icon(fn (User $record): string => $record->is_blocked ? 'heroicon-o-check-circle' : 'heroicon-o-no-symbol')
                        ->color(fn (User $record): string => $record->is_blocked ? 'success' : 'danger')
                        ->requiresConfirmation()
                        ->action(function (User $record): void {
                            $record->update(['is_blocked' => ! $record->is_blocked]);
                        }),
                ])
                    ->label('Action'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAccounts::route('/'),
            'create' => CreateAccount::route('/create'),
            'edit' => EditAccount::route('/{record}/edit'),
        ];
    }
}
