<?php

namespace App\Console\Commands;

use App\Models\CreditLog;
use App\Models\HideShowProfile;
use App\Models\ProviderProfile;
use App\Services\WalletLedgerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DeductDailyCredits extends Command
{
    public function __construct(
        private WalletLedgerService $walletLedgerService,
    ) {
        parent::__construct();
    }

    protected $signature = 'credits:deduct-daily';

    protected $description = 'Deduct 1 credit per day per visible approved profile and hide unpaid listings';

    public function handle(): int
    {
        // Find approved profiles that are currently visible.
        // A profile is visible when hide_show_profiles.status = 'show' OR no hide_show_profiles row exists
        // (which defaults to visible).
        $eligibleProfiles = ProviderProfile::query()
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
            ->orderBy('id')
            ->get(['id', 'user_id']);

        if ($eligibleProfiles->isEmpty()) {
            $this->info('No eligible visible profiles found. No credits deducted.');

            return self::SUCCESS;
        }

        $deducted = 0;
        $hiddenUnpaid = 0;

        $eligibleProfiles->each(function (ProviderProfile $profile) use (&$deducted, &$hiddenUnpaid): void {
            [$deductedForProfile, $hiddenForProfile] = DB::transaction(function () use ($profile): array {
                $locked = ProviderProfile::lockForUpdate()->find($profile->id);

                if (! $locked) {
                    return [0, 0];
                }

                if ((int) $locked->credits <= 0) {
                    HideShowProfile::updateOrCreate(
                        ['user_id' => $locked->user_id, 'provider_profile_id' => $locked->id],
                        ['user_id' => $locked->user_id, 'status' => 'hide']
                    );

                    return [0, 1];
                }

                $currentBalance = $this->walletLedgerService->currentBalance($locked);

                if ($currentBalance <= 0) {
                    HideShowProfile::updateOrCreate(
                        ['user_id' => $locked->user_id, 'provider_profile_id' => $locked->id],
                        ['user_id' => $locked->user_id, 'status' => 'hide']
                    );

                    return [0, 1];
                }

                $this->walletLedgerService->record(
                    $locked,
                    -1,
                    'daily_deduction',
                    "Your current credits balance is {$currentBalance}. You are charged 1 credit per day while your profile is visible.",
                    null,
                    'debit',
                );

                if ($currentBalance - 1 <= 0) {
                    HideShowProfile::updateOrCreate(
                        ['user_id' => $locked->user_id, 'provider_profile_id' => $locked->id],
                        ['user_id' => $locked->user_id, 'status' => 'hide']
                    );

                    return [1, 1];
                }

                return [1, 0];
            });

            $deducted += $deductedForProfile;
            $hiddenUnpaid += $hiddenForProfile;
        });

        Log::info('Daily credit deduction completed', [
            'deducted_profiles' => $deducted,
            'hidden_unpaid_profiles' => $hiddenUnpaid,
        ]);

        $this->info("Daily credit deduction completed. Deducted profiles: {$deducted}, Hidden unpaid profiles: {$hiddenUnpaid}.");

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
