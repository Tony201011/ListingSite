<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Jobs\SendFeaturedPurchaseEmailJob;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use App\Models\User;
use App\Services\WalletLedgerService;
use Illuminate\Support\Facades\DB;

class PurchaseFeatured
{
    private const AD_TIER_DURATION_DAYS = 1;

    public function __construct(
        private WalletLedgerService $walletLedgerService,
    ) {}

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
        $durationDays = self::AD_TIER_DURATION_DAYS;

        [$creditCost, $expiryColumn, $tierLabel] = $this->resolveTier($tier, $settings);
        $availableCredits = $this->walletLedgerService->currentBalance($profile);

        if ($availableCredits < $creditCost) {
            return new ActionResult(
                false,
                422,
                "You need {$creditCost} credits to activate this ad. You currently have {$availableCredits} credits on this profile.",
                $this->buildPayload($profile, $tier, $creditCost, $durationDays, $expiryColumn),
                'domain'
            );
        }

        $isExtension = false;

        DB::transaction(function () use ($user, $profile, $creditCost, $durationDays, $expiryColumn, $tier, $tierLabel, &$isExtension): void {
            $currentExpiry = $profile->{$expiryColumn};
            $isCurrent = $currentExpiry && $currentExpiry->isFuture();
            $isExtension = $isCurrent;
            $now = now();

            // Extend from current expiry when still active, otherwise start fresh.
            // Use copy() to avoid mutating $now, which is referenced later to set
            // free_listing_expires_at to the current moment.
            $baseDate = $isCurrent ? $currentExpiry : $now;
            $newExpiry = $baseDate->copy()->addDays($durationDays);

            if ($profile->free_listing_expires_at?->isFuture()) {
                $profile->free_listing_expires_at = $now;
            }

            if ($tier === self::TIER_NORMAL) {
                $profile->is_featured = true;
                $profile->featured_expires_at = $newExpiry;
            } else {
                $profile->{$expiryColumn} = $newExpiry;
            }

            $profile->save();

            $this->walletLedgerService->record(
                $profile,
                -$creditCost,
                'used',
                "Activated {$tierLabel} for {$durationDays} day",
                null,
                'debit',
            );
        });

        $profile->refresh();

        $expiresAtFormatted = $profile->{$expiryColumn}?->format('d M Y') ?? '';

        SendFeaturedPurchaseEmailJob::dispatch(
            $user->email,
            $user->name,
            $tierLabel,
            $creditCost,
            $durationDays,
            $expiresAtFormatted,
            $isExtension,
        );

        return ActionResult::success(
            $this->buildPayload($profile, $tier, $creditCost, $durationDays, $expiryColumn),
            "{$tierLabel} activated! Your listing is now boosted for {$durationDays} day."
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
