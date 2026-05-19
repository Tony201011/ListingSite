<?php

namespace App\Console\Commands;

use App\Models\CreditLog;
use App\Models\HideShowProfile;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeductDailyCredits extends Command
{
    protected $signature = 'credits:deduct-daily';

    protected $description = 'Deduct 1 credit per day per visible approved profile and hide unpaid listings';

    public function handle(): int
    {
        // Find approved profiles that are currently visible.
        // A profile is visible when hide_show_profiles.status = 'show' OR no hide_show_profiles row exists
        // (which defaults to visible).
        $eligibleProfilesByUser = ProviderProfile::query()
            ->where('profile_status', 'approved')
            ->where('is_blocked', false)
            ->whereNull('deleted_at')
            ->where(function ($query): void {
                $query->whereExists(function ($sub): void {
                    $sub->select(DB::raw(1))
                        ->from('hide_show_profiles')
                        ->whereColumn('hide_show_profiles.provider_profile_id', 'provider_profiles.id')
                        ->where('hide_show_profiles.status', 'show');
                })->orWhereNotExists(function ($sub): void {
                    $sub->select(DB::raw(1))
                        ->from('hide_show_profiles')
                        ->whereColumn('hide_show_profiles.provider_profile_id', 'provider_profiles.id');
                });
            })
            // Skip profiles that are still within their free listing period
            ->where(function ($query): void {
                $query->whereNull('free_listing_expires_at')
                    ->orWhere('free_listing_expires_at', '<=', now());
            })
            ->orderBy('user_id')
            ->orderBy('id')
            ->get(['id', 'user_id'])
            ->groupBy('user_id');

        if ($eligibleProfilesByUser->isEmpty()) {
            $this->info('No eligible visible profiles found. No credits deducted.');

            return self::SUCCESS;
        }

        $deducted = 0;
        $hiddenUnpaid = 0;

        User::whereIn('id', $eligibleProfilesByUser->keys())
            ->each(function (User $user) use (&$deducted, &$hiddenUnpaid, $eligibleProfilesByUser): void {
                /** @var Collection<int, ProviderProfile> $profiles */
                $profiles = $eligibleProfilesByUser->get($user->id, collect())->values();
                if ($profiles->isEmpty()) {
                    return;
                }

                [$deductedForUser, $hiddenForUser] = DB::transaction(function () use ($user, $profiles): array {
                    $locked = User::lockForUpdate()->find($user->id);

                    if (! $locked) {
                        return [0, 0];
                    }

                    $eligibleCount = $profiles->count();
                    $payableCount = max(0, min($locked->credits, $eligibleCount));
                    $profileIdsToHide = $profiles->skip($payableCount)->pluck('id');

                    for ($i = 0; $i < $payableCount; $i++) {
                        $currentBalance = $locked->credits;
                        $locked->decrement('credits', 1);
                        $locked->refresh();

                        CreditLog::create([
                            'user_id' => $locked->id,
                            'amount' => -1,
                            'type' => 'daily_deduction',
                            'description' => "Your current credits balance is {$currentBalance}. You are charged 1 credit per day while your profile is visible.",
                        ]);
                    }

                    foreach ($profileIdsToHide as $profileId) {
                        HideShowProfile::updateOrCreate(
                            ['user_id' => $locked->id, 'provider_profile_id' => $profileId],
                            ['user_id' => $locked->id, 'status' => 'hide']
                        );
                    }

                    return [$payableCount, $profileIdsToHide->count()];
                });

                $deducted += $deductedForUser;
                $hiddenUnpaid += $hiddenForUser;
            });

        Log::info('Daily credit deduction completed', [
            'deducted_profiles' => $deducted,
            'hidden_unpaid_profiles' => $hiddenUnpaid,
        ]);

        $this->info("Daily credit deduction completed. Deducted profiles: {$deducted}, Hidden unpaid profiles: {$hiddenUnpaid}.");

        return self::SUCCESS;
    }
}
