<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class DeleteUserAccount
{
    public function execute(User $user, int $purgeAfterDays = 30): void
    {
        DB::transaction(function () use ($user, $purgeAfterDays) {
            $user->account_status = 'soft_deleted';
            $user->scheduled_purge_at = now()->addDays($purgeAfterDays);
            $user->setRememberToken(null);
            $user->save();

            if (method_exists($user, 'tokens')) {
                $user->tokens()->delete();
            }

            $user->delete(); // soft delete only
        });
    }
}
