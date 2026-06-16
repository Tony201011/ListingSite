<?php

namespace App\Console\Commands;

use App\Actions\LogAccountLifecycleEvent;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PurgeDeletedAccounts extends Command
{
    protected $signature = 'accounts:purge-deleted';

    protected $description = 'Permanently delete or anonymise soft-deleted accounts after retention expires';

    public function __construct(private LogAccountLifecycleEvent $logAccountLifecycleEvent)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $processed = 0;
        $failed = 0;

        User::onlyTrashed()
            ->whereNotNull('scheduled_purge_at')
            ->where('scheduled_purge_at', '<=', now())
            ->whereDoesntHave('providerProfiles', fn ($q) => $q->withTrashed()->whereNotNull('hold_reason'))
            ->chunkById(100, function ($users) use (&$processed, &$failed): void {
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
        DB::transaction(function () use ($user): void {
            $random = Str::lower(Str::random(10));
            $anonymizedEmail = "deleted_user_{$user->id}_{$random}@example.deleted";

            $user->forceFill([
                'name' => 'Deleted User',
                'email' => $anonymizedEmail,
                'password' => '',
                'mobile' => null,
                'profile_image' => null,
                'referral_code' => null,
                'email_verified_at' => null,
                'mobile_verified' => false,
                'remember_token' => null,
                'account_status' => 'anonymized',
            ])->save();

            $user->providerProfiles()->withTrashed()->update([
                'name' => 'Deleted Profile',
                'phone' => null,
                'whatsapp' => null,
                'twitter_handle' => null,
                'website' => null,
                'onlyfans_username' => null,
                'suburb' => null,
                'anonymized_at' => now(),
            ]);

            $user->accountRestoreRequests()->delete();

            $this->logAccountLifecycleEvent->execute(
                userId: $user->id,
                actionType: 'account_anonymized',
                metadata: ['anonymized_email' => $anonymizedEmail]
            );

            $this->logAccountLifecycleEvent->execute(
                userId: $user->id,
                actionType: 'account_permanently_deleted',
                metadata: ['deleted_at' => now()->toIso8601String()]
            );

            $user->forceDelete();
        });
    }
}
