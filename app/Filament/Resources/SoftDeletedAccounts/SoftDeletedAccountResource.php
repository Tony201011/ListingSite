<?php

namespace App\Filament\Resources\SoftDeletedAccounts;

use App\Actions\LogAccountLifecycleEvent;
use App\Actions\RestoreSoftDeletedAccount;
use App\Filament\Resources\SoftDeletedAccounts\Pages\ListSoftDeletedAccounts;
use App\Jobs\SendRestoreAccountEmailJob;
use App\Models\AccountRestoreRequest;
use App\Models\User;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class SoftDeletedAccountResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationLabel = 'Soft Deleted';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-trash';

    protected static string|UnitEnum|null $navigationGroup = 'Account Management';

    protected static ?string $slug = 'account-management/soft-deleted';

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->onlyTrashed()
            ->where('role', User::ROLE_PROVIDER)
            ->with(['providerProfiles' => fn ($q) => $q->withTrashed(), 'accountRestoreRequests']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
                TextColumn::make('email')->searchable(),
                TextColumn::make('role')->label('Account Type')->badge(),
                TextColumn::make('deleted_at')->label('Deleted Date')->dateTime()->sortable(),
                TextColumn::make('scheduled_purge_at')->label('Scheduled Purge Date')->dateTime()->sortable(),
                TextColumn::make('remaining_days')
                    ->label('Remaining Days')
                    ->state(fn (User $record): int => max(0, (int) now()->startOfDay()->diffInDays($record->scheduled_purge_at?->startOfDay() ?? now(), false))),
                TextColumn::make('restore_request_status')
                    ->label('Restore Request Status')
                    ->state(function (User $record): string {
                        return $record->accountRestoreRequests()->latest('id')->value('status') ?? '-';
                    })
                    ->badge(),
                TextColumn::make('provider_profile_status')
                    ->label('Provider Profile Status')
                    ->state(function (User $record): string {
                        $statuses = $record->providerProfiles()->withTrashed()->pluck('profile_status')->filter()->unique()->values();

                        return $statuses->isEmpty() ? '-' : $statuses->implode(', ');
                    }),
            ])
            ->defaultSort('deleted_at', 'desc')
            ->recordActions([
                ActionGroup::make([
                    Action::make('restoreAccount')
                        ->label('Restore Account')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (User $record, RestoreSoftDeletedAccount $restoreSoftDeletedAccount): void {
                            $restoreSoftDeletedAccount->execute($record, auth('admin')->id() ?? auth()->id());
                        }),
                    Action::make('approveRestoreRequest')
                        ->label('Approve Restore Request')
                        ->form([
                            Textarea::make('admin_reply')
                                ->label('Reply Message (optional)')
                                ->rows(3),
                        ])
                        ->requiresConfirmation()
                        ->action(function (User $record, array $data, RestoreSoftDeletedAccount $restoreSoftDeletedAccount): void {
                            $request = $record->accountRestoreRequests()
                                ->where('status', AccountRestoreRequest::STATUS_PENDING)
                                ->latest('id')
                                ->first();

                            if ($request) {
                                if (filled($data['admin_reply'] ?? null)) {
                                    $request->forceFill(['admin_reply' => $data['admin_reply']])->save();
                                }

                                $restoreSoftDeletedAccount->execute($record, auth('admin')->id() ?? auth()->id(), $request);
                            }
                        }),
                    Action::make('rejectRestoreRequest')
                        ->label('Reject Restore Request')
                        ->color('warning')
                        ->form([
                            Textarea::make('admin_reply')
                                ->label('Reply Message (optional)')
                                ->rows(3),
                        ])
                        ->requiresConfirmation()
                        ->action(function (User $record, array $data, LogAccountLifecycleEvent $logAccountLifecycleEvent): void {
                            $request = $record->accountRestoreRequests()
                                ->where('status', AccountRestoreRequest::STATUS_PENDING)
                                ->latest('id')
                                ->first();

                            if (! $request) {
                                return;
                            }

                            $request->forceFill([
                                'status' => AccountRestoreRequest::STATUS_REJECTED,
                                'admin_reply' => $data['admin_reply'] ?? null,
                                'reviewed_by' => auth('admin')->id() ?? auth()->id(),
                                'reviewed_at' => now(),
                            ])->save();

                            $logAccountLifecycleEvent->execute(
                                userId: $record->id,
                                actionType: 'restore_request_rejected',
                                adminId: auth('admin')->id() ?? auth()->id(),
                                metadata: ['restore_request_id' => $request->id]
                            );

                            SendRestoreAccountEmailJob::dispatch($record->id, 'restore_request_rejected', $request->id);
                        }),
                    Action::make('permanentDelete')
                        ->label('Permanently Delete Account')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (User $record, LogAccountLifecycleEvent $logAccountLifecycleEvent): void {
                            $record->accountRestoreRequests()->delete();

                            $logAccountLifecycleEvent->execute(
                                userId: $record->id,
                                actionType: 'account_permanently_deleted',
                                adminId: auth('admin')->id() ?? auth()->id(),
                                metadata: ['manual' => true]
                            );

                            $record->forceDelete();
                        }),
                ])
                    ->label('Action'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSoftDeletedAccounts::route('/'),
        ];
    }
}
