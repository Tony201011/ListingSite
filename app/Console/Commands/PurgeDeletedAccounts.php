<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PurgeDeletedAccounts extends Command
{
    protected $signature = 'accounts:purge-deleted';

    protected $description = 'Anonymize and permanently delete soft-deleted accounts whose retention period has expired';

    public function handle(): int
    {
        $processed = 0;
        $failed = 0;

        User::onlyTrashed()
            ->whereNotNull('scheduled_purge_at')
            ->where('scheduled_purge_at', '<=', now())
            ->whereNull('hold_reason')
            ->chunkById(100, function ($users) use (&$processed, &$failed) {
                foreach ($users as $user) {
                    try {
                        $this->purgeUser($user);
                        $processed++;
                        $this->info("Purged user ID {$user->id}");
                    } catch (\Throwable $e) {
                        $failed++;
                        report($e);
                        $this->error("Failed for user ID {$user->id}: {$e->getMessage()}");
                    }
                }
            });

        $this->info("Purge complete: {$processed} processed, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    protected function purgeUser(User $user): void
    {
        $random = Str::uuid()->toString();

        // Anonymize PII before hard-deleting so audit logs / backups
        // don't retain identifiable data.
        $user->forceFill([
            'name' => 'Deleted User',
            'email' => "deleted+{$random}@example.invalid",
            'password' => '',
            'mobile' => null,
            'profile_image' => null,
            'referral_code' => null,
            'email_verified_at' => null,
            'mobile_verified' => false,
            'remember_token' => null,
            'anonymized_at' => now(),
            'account_status' => 'anonymized',
        ])->save();

        // Hard-delete the user; FK cascades remove profile_images, rates,
        // rate_groups, tours, short_urls, provider_profile, etc.
        $user->forceDelete();
    }
}
