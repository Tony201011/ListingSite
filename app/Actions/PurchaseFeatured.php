<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\CreditLog;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use App\Models\User;
use Carbon\CarbonInterface;
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
     */
    public function execute(User $user, ProviderProfile $profile, string $tier = self::TIER_NORMAL): ActionResult
    {
        $settings = SiteSetting::getAdTierSettings();
        $durationDays = $settings['featured_duration_days'];

        [$creditCost, $expiryColumn, $tierLabel] = $this->resolveTier($tier, $settings);
        $isExtension = false;
        $previousExpiry = null;
        $newExpiry = null;

        if ($user->credits < $creditCost) {
            return new ActionResult(
                false,
                422,
                "You need {$creditCost} credits to activate this ad. You currently have {$user->credits} credits.",
                $this->buildPayload($profile, $tier, $creditCost, $durationDays, $expiryColumn),
                'domain'
            );
        }

        DB::transaction(function () use ($user, $profile, $creditCost, $durationDays, $expiryColumn, $tier, $tierLabel, &$isExtension, &$previousExpiry, &$newExpiry): void {
            $currentExpiry = $profile->{$expiryColumn};
            $isCurrent = $currentExpiry instanceof CarbonInterface && $currentExpiry->isFuture();
            $isExtension = $isCurrent;
            $previousExpiry = $isCurrent ? $currentExpiry->copy() : null;

            // Extend from current expiry when still active, otherwise start fresh
            $baseDate = $isCurrent ? $currentExpiry->copy() : now();
            $newExpiry = $baseDate->addDays($durationDays);

            if ($tier === self::TIER_NORMAL) {
                $profile->is_featured = true;
                $profile->featured_expires_at = $newExpiry;
            } else {
                $profile->{$expiryColumn} = $newExpiry;
            }

            $profile->save();

            $user->decrement('credits', $creditCost);

            CreditLog::create([
                'user_id' => $user->id,
                'amount' => -$creditCost,
                'type' => 'used',
                'description' => "Activated {$tierLabel} for {$durationDays} days",
                'reference_type' => ProviderProfile::class,
                'reference_id' => $profile->id,
            ]);
        });

        $profile->refresh();
        app(SendFeaturedPurchaseEmail::class)->execute(
            $user,
            $profile,
            $tierLabel,
            $creditCost,
            $durationDays,
            $newExpiry,
            $isExtension,
            $previousExpiry,
        );

        return ActionResult::success(
            $this->buildPayload($profile, $tier, $creditCost, $durationDays, $expiryColumn),
            $isExtension
                ? "{$tierLabel} extended! Your listing boost has been extended by {$durationDays} days."
                : "{$tierLabel} activated! Your listing is now boosted for {$durationDays} days."
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
                'Featured Badge',
            ],
        };
    }

    private function buildPayload(ProviderProfile $profile, string $tier, int $creditCost, int $durationDays, string $expiryColumn): array
    {
        $expiresAt = $tier === self::TIER_NORMAL
            ? $profile->featured_expires_at?->toIso8601String()
            : $profile->{$expiryColumn}?->toIso8601String();

        return [
            'tier' => $tier,
            'is_featured' => (bool) $profile->is_featured,
            'expires_at' => $expiresAt,
            'credit_cost' => $creditCost,
            'duration_days' => $durationDays,
        ];
    }
}
