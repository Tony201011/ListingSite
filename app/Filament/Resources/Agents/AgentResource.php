<?php

namespace App\Filament\Resources\Agents;

use App\Filament\Resources\Agents\Pages\CreateAgent;
use App\Filament\Resources\Agents\Pages\EditAgent;
use App\Filament\Resources\Agents\Pages\ListAgents;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AgentResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $navigationLabel = 'Agent Listing';

    protected static ?string $modelLabel = 'Agent';

    protected static ?string $pluralModelLabel = 'Agents';

    protected static ?string $slug = 'agents';

    protected static ?int $navigationSort = 1;

    protected static function isCreatePage(): bool
    {
        return request()->routeIs('filament.admin.resources.agents.create');
    }

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
        return $schema->components([
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
                ->required(fn (): bool => static::isCreatePage())
                ->minLength(8)
                ->same('passwordConfirmation')
                ->dehydrated(fn ($state): bool => filled($state))
                ->suffixAction(
                    Action::make('generatePassword')
                        ->label('Generate')
                        ->icon('heroicon-o-sparkles')
                        ->action(function (Set $set): void {
                            $password = Str::password(16, symbols: true);
                            $set('password', $password);
                            $set('passwordConfirmation', $password);
                            Notification::make()
                                ->title('Password generated')
                                ->body($password)
                                ->success()
                                ->persistent()
                                ->send();
                        })
                ),
            TextInput::make('passwordConfirmation')
                ->label('Confirm Password')
                ->password()
                ->revealable(filament()->arePasswordsRevealable())
                ->required(fn (): bool => static::isCreatePage() || filled(request()->input('password')))
                ->visible(fn (): bool => static::isCreatePage())
                ->dehydrated(false),
        ]);
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
                EditAction::make()->label('Edit'),
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
            'index' => ListAgents::route('/'),
            'create' => CreateAgent::route('/create'),
            'edit' => EditAgent::route('/{record}/edit'),
        ];
    }
}