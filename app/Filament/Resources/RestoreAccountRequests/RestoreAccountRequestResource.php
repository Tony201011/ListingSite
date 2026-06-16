<?php

namespace App\Filament\Resources\RestoreAccountRequests;

use App\Actions\LogAccountLifecycleEvent;
use App\Actions\RestoreSoftDeletedAccount;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;
use App\Filament\Resources\RestoreAccountRequests\Pages\ListRestoreAccountRequests;
use App\Jobs\SendRestoreAccountEmailJob;
use App\Models\AccountRestoreRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class RestoreAccountRequestResource extends Resource
{
    protected static ?string $model = AccountRestoreRequest::class;

    protected static ?string $navigationLabel = 'Restore Requests';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-arrow-path';

    protected static string|UnitEnum|null $navigationGroup = 'Account Management';

    protected static ?string $slug = 'account-management/restore-requests';

    public static function canAccess(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('user.name')->label('Name')->searchable(),
                TextColumn::make('user.email')->label('Email')->searchable(),
                TextColumn::make('user.role')->label('Account Type')->badge(),
                TextColumn::make('user.deleted_at')->label('Deleted Date')->dateTime()->placeholder('-')->sortable(),
                TextColumn::make('request_reason')->label('Reason')->limit(80)->toggleable(),
                TextColumn::make('admin_reply')->label('Admin Reply')->limit(80)->placeholder('-')->toggleable(),
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('reviewer.name')->label('Reviewed By')->placeholder('-'),
                TextColumn::make('reviewed_at')->dateTime()->placeholder('-')->sortable(),
                TextColumn::make('created_at')->label('Submitted Date')->dateTime()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    AccountRestoreRequest::STATUS_PENDING => 'Pending',
                    AccountRestoreRequest::STATUS_APPROVED => 'Approved',
                    AccountRestoreRequest::STATUS_REJECTED => 'Rejected',
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ActionGroup::make([
                    Action::make('reply')
                        ->label('Add Reply')
                        ->icon('heroicon-o-chat-bubble-left-ellipsis')
                        ->color('info')
                        ->form([
                            Textarea::make('admin_reply')
                                ->label('Reply Message')
                                ->required()
                                ->rows(4),
                        ])
                        ->action(function (AccountRestoreRequest $record, array $data, LogAccountLifecycleEvent $logAccountLifecycleEvent): void {
                            $record->forceFill([
                                'admin_reply' => $data['admin_reply'],
                            ])->save();

                            $logAccountLifecycleEvent->execute(
                                userId: $record->user_id,
                                actionType: 'admin_reply_added',
                                adminId: auth('admin')->id() ?? auth()->id(),
                                metadata: ['restore_request_id' => $record->id]
                            );

                            SendRestoreAccountEmailJob::dispatch($record->user_id, 'restore_request_replied', $record->id);
                        }),
                    Action::make('approve')
                        ->label('Approve')
                        ->color('success')
                        ->visible(fn (AccountRestoreRequest $record): bool => $record->status === AccountRestoreRequest::STATUS_PENDING)
                        ->form([
                            Textarea::make('admin_reply')
                                ->label('Reply Message (optional)')
                                ->rows(3),
                        ])
                        ->requiresConfirmation()
                        ->action(function (AccountRestoreRequest $record, array $data, RestoreSoftDeletedAccount $restoreSoftDeletedAccount): void {
                            $user = $record->user;

                            if (! $user || ! $user->trashed()) {
                                return;
                            }

                            if (filled($data['admin_reply'] ?? null)) {
                                $record->forceFill(['admin_reply' => $data['admin_reply']])->save();
                            }

                            $restoreSoftDeletedAccount->execute(
                                $user,
                                auth('admin')->id() ?? auth()->id(),
                                $record
                            );
                        }),
                    Action::make('reject')
                        ->label('Reject')
                        ->color('danger')
                        ->visible(fn (AccountRestoreRequest $record): bool => $record->status === AccountRestoreRequest::STATUS_PENDING)
                        ->form([
                            Textarea::make('admin_reply')
                                ->label('Reply Message (optional)')
                                ->rows(3),
                        ])
                        ->requiresConfirmation()
                        ->action(function (AccountRestoreRequest $record, array $data, LogAccountLifecycleEvent $logAccountLifecycleEvent): void {
                            $record->forceFill([
                                'status' => AccountRestoreRequest::STATUS_REJECTED,
                                'admin_reply' => $data['admin_reply'] ?? null,
                                'reviewed_by' => auth('admin')->id() ?? auth()->id(),
                                'reviewed_at' => now(),
                            ])->save();

                            $logAccountLifecycleEvent->execute(
                                userId: $record->user_id,
                                actionType: 'restore_request_rejected',
                                adminId: auth('admin')->id() ?? auth()->id(),
                                metadata: ['restore_request_id' => $record->id]
                            );

                            SendRestoreAccountEmailJob::dispatch($record->user_id, 'restore_request_rejected', $record->id);
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRestoreAccountRequests::route('/'),
        ];
    }
}
