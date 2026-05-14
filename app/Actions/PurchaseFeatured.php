<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\CreditLog;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PurchaseFeatured
{
    /** Valid tier identifiers */
    public const TIER_NORMAL = 'normal';

    public const TIER_HOME_FEATURED = 'home_page';

    public const TIER_LOCAL_BANNER = 'local_banner';

    public const TIER_HOME_BANNER = 'home_banner';

    public const TIERS = [
        self::TIER_NORMAL,
        self::TIER_HOME_FEATURED,
        self::TIER_LOCAL_BANNER,
        self::TIER_HOME_BANNER,
    ];

    /**
     * Purchase or extend a featured ad tier for a provider profile.
     *
     * @param  string  $tier  One of the TIER_* constants (defaults to 'normal' for backward compatibility).
     * @param  int  $days  Number of days to purchase (1 credit-per-day unit × days = total cost).
     */
    public function execute(User $user, ProviderProfile $profile, string $tier = self::TIER_NORMAL, int $days = 1): ActionResult
    {
        $settings = SiteSetting::getAdTierSettings();

        [$creditCostPerDay, $expiryColumn, $tierLabel] = $this->resolveTier($tier, $settings);

        $totalCost = $creditCostPerDay * $days;

        if ($user->credits < $totalCost) {
            return new ActionResult(
                false,
                422,
                "You need {$totalCost} credits to activate this ad for {$days} day(s). You currently have {$user->credits} credits.",
                $this->buildPayload($profile, $tier, $creditCostPerDay, $days, $expiryColumn),
                'domain'
            );
        }

        DB::transaction(function () use ($user, $profile, $totalCost, $creditCostPerDay, $days, $expiryColumn, $tier, $tierLabel): void {
            $currentExpiry = $profile->{$expiryColumn};
            $isCurrent = $currentExpiry && $currentExpiry->isFuture();

            // Extend from current expiry when still active, otherwise start fresh
            // Use copy() to avoid mutating the original Carbon instance on the profile
            $baseDate = $isCurrent ? $currentExpiry->copy() : now();
            $newExpiry = $baseDate->addDays($days);

            if ($tier === self::TIER_NORMAL) {
                $profile->is_featured = true;
                $profile->featured_expires_at = $newExpiry;
            } else {
                $profile->{$expiryColumn} = $newExpiry;
            }

            $profile->save();

            $user->decrement('credits', $totalCost);

            CreditLog::create([
                'user_id' => $user->id,
                'amount' => -$totalCost,
                'type' => 'used',
                'description' => "Activated {$tierLabel} for {$days} day(s) at {$creditCostPerDay} credit(s)/day",
                'reference_type' => ProviderProfile::class,
                'reference_id' => $profile->id,
            ]);
        });

        $profile->refresh();

        return ActionResult::success(
            $this->buildPayload($profile, $tier, $creditCostPerDay, $days, $expiryColumn),
            "{$tierLabel} activated! Your listing is now boosted for {$days} day(s)."
        );
    }

    /**
     * Resolve cost, expiry column and human label for the given tier.
     *
     * @return array{0: int, 1: string, 2: string}
     */
    private function resolveTier(string $tier, array $settings): array
    {
        return match ($tier) {
            self::TIER_HOME_BANNER => [
                $settings['home_banner_credit_cost'],
                'home_banner_expires_at',
                'Home Page Banner',
            ],
            self::TIER_HOME_FEATURED => [
                $settings['home_featured_credit_cost'],
                'home_featured_expires_at',
                'Home Page Featured',
            ],
            self::TIER_LOCAL_BANNER => [
                $settings['local_banner_credit_cost'],
                'local_banner_expires_at',
                'Local Banner',
            ],
            default => [
                $settings['normal_featured_credit_cost'],
                'featured_expires_at',
                'Featured Listing',
            ],
        };
    }

    private function buildPayload(ProviderProfile $profile, string $tier, int $creditCostPerDay, int $days, string $expiryColumn): array
    {
        $expiresAt = $tier === self::TIER_NORMAL
            ? $profile->featured_expires_at?->toIso8601String()
            : $profile->{$expiryColumn}?->toIso8601String();

        return [
            'tier' => $tier,
            'is_featured' => (bool) $profile->is_featured,
            'expires_at' => $expiresAt,
            'credit_cost' => $creditCostPerDay,
            'credit_cost_per_day' => $creditCostPerDay,
            'duration_days' => $days,
        ];
    }
}
