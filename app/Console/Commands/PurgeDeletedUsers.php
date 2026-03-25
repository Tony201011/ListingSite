<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class PurgeDeletedUsers extends Command
{
    protected $signature = 'users:purge-deleted';

    protected $description = 'Anonymize or permanently delete users whose retention period has expired';

    public function handle(): int
    {
        User::onlyTrashed()
            ->whereNotNull('scheduled_purge_at')
            ->where('scheduled_purge_at', '<=', now())
            ->chunkById(100, function ($users): void {
                foreach ($users as $user) {
                    $this->purgeUser($user);
                }
            });

        $this->info('Deleted user cleanup complete.');

        return self::SUCCESS;
    }

    protected function purgeUser(User $user): void
    {
        // Option 1: hard delete directly
        // $user->forceDelete();

        // Option 2: anonymize first, then hard delete
        $random = Str::uuid()->toString();

        $user->update([
            'name' => 'Deleted User',
            'email' => 'deleted+' . $random . '@example.invalid',
            'mobile' => null,
            'suburb' => null,
            'profile_image' => null,
            'referral_code' => null,
            'otp' => null,
            'otp_expires_at' => null,
            'email_verified_at' => null,
            'mobile_verified' => false,
            'remember_token' => null,
            'anonymized_at' => now(),
        ]);

        // delete related private data if needed
        optional($user->providerProfile)->delete();

        // hard delete user after anonymization
        $user->forceDelete();
    }
}
