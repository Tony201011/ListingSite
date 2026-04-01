<?php

namespace App\Filament\Resources\Agents;

use App\Filament\Resources\Agents\Pages\ManageAgents;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class AgentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Agent Listing';

    protected static ?string $modelLabel = 'Agent';

    protected static ?string $pluralModelLabel = 'Agents';

    protected static ?string $slug = 'agents';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withCount('managedProfiles')
            ->where('role', User::ROLE_AGENT);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('profile_image')
                    ->label('')
                    ->disk(fn (): string => config('filesystems.default', 'public'))
                    ->circular()
                    ->defaultImageUrl(fn (User $record): string => 'https://ui-avatars.com/api/?name='.urlencode($record->name).'&background=E5E7EB&color=111827')
                    ->size(40),
                TextColumn::make('name')
                    ->searchable()
                    ->weight('semibold')
                    ->description(fn (User $record): string => $record->email),
                TextColumn::make('managed_profiles_count')
                    ->label('Profiles')
                    ->sortable(),
                TextColumn::make('account_status')
                    ->label('Account')
                    ->badge()
                    ->state(fn (User $record): string => $record->is_blocked ? 'Blocked' : 'Active')
                    ->color(fn (string $state): string => $state === 'Blocked' ? 'danger' : 'success'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (User $record): string => filled($record->email_verified_at) ? 'Verified' : 'Unverified')
                    ->color(fn (string $state): string => $state === 'Verified' ? 'success' : 'warning'),
                TextColumn::make('created_at')
                    ->label('Joined')
                    ->dateTime()
                    ->sortable()
                    ->since(),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Edit')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('password')
                            ->password()
                            ->revealable(filament()->arePasswordsRevealable())
                            ->minLength(8)
                            ->dehydrated(fn ($state): bool => filled($state)),
                    ])
                    ->using(function (array $data, User $record): void {
                        $record->update(array_filter([
                            'name' => $data['name'],
                            'email' => $data['email'],
                            'password' => $data['password'] ?? null,
                        ], fn ($value): bool => $value !== null));
                    }),
                Action::make('block')
                    ->label('Block')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => ! $record->is_blocked)
                    ->action(fn (User $record): bool => $record->update(['is_blocked' => true]))
                    ->icon('heroicon-o-lock-closed'),
                Action::make('unblock')
                    ->label('Unblock')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->is_blocked)
                    ->action(fn (User $record): bool => $record->update(['is_blocked' => false]))
                    ->icon('heroicon-o-lock-open'),
                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Delete agent')
                    ->modalDescription('Are you sure you want to delete this agent? This will soft-delete the user account.')
                    ->action(fn (User $record): ?bool => $record->delete())
                    ->successNotificationTitle('Agent deleted'),
            ])
            ->toolbarActions([])
            ->defaultSort('created_at', 'desc')
            ->striped()
            ->emptyStateHeading('No agents yet')
            ->emptyStateDescription('Create your first agent to start managing agent accounts here.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAgents::route('/'),
        ];
    }
}