<?php

namespace App\Console\Commands;

use App\Models\CreditLog;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeductDailyCredits extends Command
{
    protected $signature = 'credits:deduct-daily';

    protected $description = 'Deduct 1 credit per day from users with at least one visible approved profile';

    public function handle(): int
    {
        // Find users who have at least one approved provider profile that is visible (show status).
        // A profile is visible when hide_show_profiles.status = 'show' OR no hide_show_profiles row exists
        // (which defaults to visible).
        $userIds = ProviderProfile::query()
            ->where('profile_status', 'approved')
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
            ->distinct()
            ->pluck('user_id');

        if ($userIds->isEmpty()) {
            $this->info('No users with visible approved profiles found. No credits deducted.');

            return self::SUCCESS;
        }

        $deducted = 0;
        $skipped = 0;

        User::whereIn('id', $userIds)->each(function (User $user) use (&$deducted, &$skipped): void {
            if ($user->credits <= 0) {
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
                ]);

                return true;
            });

            if ($wasDeducted) {
                $deducted++;
            } else {
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
}
