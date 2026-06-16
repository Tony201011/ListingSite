<?php

namespace App\Actions;

use App\Models\AccountRestoreRequest;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class RestoreSoftDeletedAccount
{
    public function __construct(private LogAccountLifecycleEvent $logAccountLifecycleEvent) {}

    public function execute(User $user, ?int $adminId = null, ?AccountRestoreRequest $restoreRequest = null): void
    {
        if (! $user->trashed()) {
            throw new RuntimeException('Account is not soft deleted.');
        }

        if ($user->scheduled_purge_at !== null && $user->scheduled_purge_at->isPast()) {
            throw new RuntimeException('Retention period has expired.');
        }

        DB::transaction(function () use ($user, $adminId, $restoreRequest): void {
            $user->restore();
            $user->providerProfiles()->onlyTrashed()->restore();

            $user->forceFill([
                'account_status' => 'active',
                'scheduled_purge_at' => null,
            ])->save();

            if ($restoreRequest) {
                $restoreRequest->forceFill([
                    'status' => AccountRestoreRequest::STATUS_APPROVED,
                    'reviewed_by' => $adminId,
                    'reviewed_at' => now(),
                ])->save();

                $this->logAccountLifecycleEvent->execute(
                    userId: $user->id,
                    actionType: 'restore_request_approved',
                    adminId: $adminId,
                    metadata: ['restore_request_id' => $restoreRequest->id]
                );
            }

            $this->logAccountLifecycleEvent->execute(
                userId: $user->id,
                actionType: 'account_restored',
                adminId: $adminId,
                metadata: ['restore_request_id' => $restoreRequest?->id]
            );
        });
    }
}
