<?php

namespace App\Console\Commands;

use App\Models\CreditLog;
use App\Models\HideShowProfile;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeductDailyCredits extends Command
{
    protected $signature = 'credits:deduct-daily';

    protected $description = 'Deduct 1 credit per day from each visible approved profile after free listing period';

    public function handle(): int
    {
        // Find approved, visible provider profiles that are outside free listing period.
        // A profile is visible when hide_show_profiles.status = 'show' OR no hide_show_profiles row exists.
        $profiles = ProviderProfile::query()
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
            ->with('user:id,credits')
            ->orderBy('user_id')
            ->orderBy('id')
            ->get(['id', 'user_id']);

        if ($profiles->isEmpty()) {
            $this->info('No eligible visible approved profiles found. No credits deducted.');

            return self::SUCCESS;
        }

        $deducted = 0;
        $skipped = 0;

        $profiles->each(function (ProviderProfile $profile) use (&$deducted, &$skipped): void {
            $user = $profile->user;
            if (! $user instanceof User) {
                $skipped++;

                return;
            }

            if ($user->credits <= 0) {
                $this->hideProfileForInsufficientCredits($profile);
                $skipped++;

                return;
            }

            $wasDeducted = DB::transaction(function () use ($user): bool {
                $locked = User::lockForUpdate()->find($user->id);

                if (! $locked || $locked->credits <= 0) {
                    return false;
                }

                $currentBalance = $locked->credits;
                $locked->decrement('credits', 1);

                CreditLog::create([
                    'user_id' => $locked->id,
                    'amount' => -1,
                    'type' => 'daily_deduction',
                    'description' => "Your current credits balance is {$currentBalance}. You are charged 1 credit per day while your profile is visible.",
                    'reference_type' => ProviderProfile::class,
                    'reference_id' => $profile->id,
                ]);

                return true;
            });

            if ($wasDeducted) {
                $deducted++;
            } else {
                $this->hideProfileForInsufficientCredits($profile);
                $skipped++;
            }
        });

        Log::info('Daily credit deduction completed', [
            'deducted' => $deducted,
            'skipped_insufficient_credits' => $skipped,
        ]);

        $this->info("Daily credit deduction completed. Deducted: {$deducted}, Skipped (zero credits): {$skipped}.");

        return self::SUCCESS;
    }

    private function hideProfileForInsufficientCredits(ProviderProfile $profile): void
    {
        HideShowProfile::updateOrCreate(
            ['provider_profile_id' => $profile->id],
            [
                'user_id' => $profile->user_id,
                'status' => 'hide',
            ]
        );
    }
}
