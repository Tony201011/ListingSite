<?php

namespace App\Filament\Resources\RestoreAccountRequests;

use App\Actions\LogAccountLifecycleEvent;
use App\Actions\RestoreSoftDeletedAccount;
use App\Filament\Resources\Pages\ListRecordsWithPageJump;
use App\Filament\Resources\RestoreAccountRequests\Pages\ListRestoreAccountRequests;
use App\Models\AccountRestoreRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Facades\Filament;
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
                TextColumn::make('status')->badge()->sortable(),
                TextColumn::make('request_reason')->label('Reason')->limit(80)->toggleable(),
                TextColumn::make('reviewer.name')->label('Reviewed By')->placeholder('-'),
                TextColumn::make('reviewed_at')->dateTime()->placeholder('-')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable(),
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
                    Action::make('approve')
                        ->label('Approve')
                        ->color('success')
                        ->visible(fn (AccountRestoreRequest $record): bool => $record->status === AccountRestoreRequest::STATUS_PENDING)
                        ->requiresConfirmation()
                        ->action(function (AccountRestoreRequest $record, RestoreSoftDeletedAccount $restoreSoftDeletedAccount): void {
                            $user = $record->user;

                            if (! $user || ! $user->trashed()) {
                                return;
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
                        ->requiresConfirmation()
                        ->action(function (AccountRestoreRequest $record, LogAccountLifecycleEvent $logAccountLifecycleEvent): void {
                            $record->forceFill([
                                'status' => AccountRestoreRequest::STATUS_REJECTED,
                                'reviewed_by' => auth('admin')->id() ?? auth()->id(),
                                'reviewed_at' => now(),
                            ])->save();

                            $logAccountLifecycleEvent->execute(
                                userId: $record->user_id,
                                actionType: 'restore_request_rejected',
                                adminId: auth('admin')->id() ?? auth()->id(),
                                metadata: ['restore_request_id' => $record->id]
                            );
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
