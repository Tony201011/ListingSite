<?php

namespace App\Filament\Resources\Agents;

use App\Filament\Resources\Agents\Pages\ListAgents;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withTrashed()
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
                ->required(fn (string $operation): bool => $operation === 'create')
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
                ->required(fn (string $operation): bool => $operation === 'create')
                ->visible(fn (string $operation): bool => $operation === 'create')
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
                    ->state(fn (User $record): string => $record->trashed() ? 'Deleted' : ($record->is_blocked ? 'Blocked' : 'Active'))
                    ->color(fn (string $state): string => match ($state) {
                        'Deleted' => 'danger',
                        'Blocked' => 'warning',
                        default => 'success',
                    }),
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
            ->filters([
                TernaryFilter::make('email_verified_at')
                    ->label('Status')
                    ->nullable()
                    ->trueLabel('Verified')
                    ->falseLabel('Unverified'),

                SelectFilter::make('is_blocked')
                    ->label('Account')
                    ->options([
                        '0' => 'Active',
                        '1' => 'Blocked',
                    ]),

                Filter::make('created_at')
                    ->label('Created Date')
                    ->schema([
                        DatePicker::make('created_from')->label('From'),
                        DatePicker::make('created_until')->label('Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                filled($data['created_from'] ?? null),
                                fn (Builder $query): Builder => $query->whereDate('created_at', '>=', $data['created_from']),
                            )
                            ->when(
                                filled($data['created_until'] ?? null),
                                fn (Builder $query): Builder => $query->whereDate('created_at', '<=', $data['created_until']),
                            );
                    }),

                SelectFilter::make('deleted_status')
                    ->label('Deleted Status')
                    ->options([
                        'deleted' => 'Deleted',
                        'not_deleted' => 'Not Deleted',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'deleted' => $query->whereNotNull((new User)->getTable().'.deleted_at'),
                            'not_deleted' => $query->whereNull((new User)->getTable().'.deleted_at'),
                            default => $query,
                        };
                    })
                    ->placeholder('All Accounts'),
            ])
            ->recordActions([
                EditAction::make()->label('Edit')
                    ->visible(fn (User $record): bool => ! $record->trashed()),
                Action::make('block')
                    ->label('Block')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => ! $record->is_blocked && ! $record->trashed())
                    ->action(fn (User $record): bool => $record->update(['is_blocked' => true]))
                    ->icon('heroicon-o-lock-closed'),
                Action::make('unblock')
                    ->label('Unblock')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (User $record): bool => $record->is_blocked && ! $record->trashed())
                    ->action(fn (User $record): bool => $record->update(['is_blocked' => false]))
                    ->icon('heroicon-o-lock-open'),
                Action::make('restore')
                    ->label('Restore')
                    ->color('success')
                    ->icon('heroicon-o-arrow-path')
                    ->requiresConfirmation()
                    ->modalHeading('Restore agent')
                    ->modalDescription('Are you sure you want to restore this agent account?')
                    ->visible(fn (User $record): bool => $record->trashed())
                    ->action(function (User $record): void {
                        $record->restore();
                    })
                    ->successNotificationTitle('Agent restored'),
                Action::make('delete')
                    ->label('Delete')
                    ->color('danger')
                    ->icon('heroicon-o-trash')
                    ->requiresConfirmation()
                    ->modalHeading('Delete agent')
                    ->modalDescription('Are you sure you want to delete this agent? This will soft-delete the user account.')
                    ->visible(fn (User $record): bool => ! $record->trashed())
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
            'create' => Pages\CreateAgent::route('/create'),
            'edit' => Pages\EditAgent::route('/{record}/edit'),
        ];
    }
}
