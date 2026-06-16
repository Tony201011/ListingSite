<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DeleteUserAccount
{
    public function __construct(private LogAccountLifecycleEvent $logAccountLifecycleEvent) {}

    public function execute(User $user, int $purgeAfterDays = 30): void
    {
        DB::transaction(function () use ($user, $purgeAfterDays): void {
            $user->account_status = 'soft_deleted';
            $user->scheduled_purge_at = now()->addDays($purgeAfterDays);
            $user->setRememberToken(null);
            $user->save();

            $user->providerProfiles()->whereNull('deleted_at')->delete();

            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->where('user_id', $user->id)->delete();
            }

            $user->delete();

            $this->logAccountLifecycleEvent->execute(
                userId: $user->id,
                actionType: 'account_soft_deleted',
                metadata: [
                    'scheduled_purge_at' => $user->scheduled_purge_at?->toIso8601String(),
                ]
            );
        });
    }
}
