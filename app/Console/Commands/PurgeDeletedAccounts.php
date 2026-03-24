<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class PurgeDeletedAccounts extends Command
{
    protected $signature = 'accounts:purge-deleted';
    protected $description = 'Permanently purge or anonymize soft-deleted accounts after retention period';

    public function handle(): int
    {
        User::onlyTrashed()
            ->whereNotNull('scheduled_purge_at')
            ->where('scheduled_purge_at', '<=', now())
            ->whereNull('hold_reason')
            ->chunkById(100, function ($users) {
                foreach ($users as $user) {
                    try {
                        $this->purgeUser($user);
                        $this->info("Processed user ID {$user->id}");
                    } catch (\Throwable $e) {
                        report($e);
                        $this->error("Failed for user ID {$user->id}: {$e->getMessage()}");
                    }
                }
            });

        return self::SUCCESS;
    }

    protected function purgeUser(User $user): void
    {
        // Option A: direct hard delete
        // $user->forceDelete();

        // Option B: anonymize first, then delete
        $random = bin2hex(random_bytes(8));

        $user->forceFill([
            'name' => 'Deleted User',
            'email' => "deleted-{$random}@example.invalid",
            'password' => null,
            'anonymized_at' => now(),
            'account_status' => 'anonymized',
        ])->save();

        $user->forceDelete();
    }
}
