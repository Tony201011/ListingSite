<?php

namespace App\Http\Controllers\Profile;

use App\Actions\GetActiveProviderProfile;
use App\Actions\GetFeaturedState;
use App\Actions\PurchaseFeatured;
use App\Actions\Subscription\HandleWooCommerceCheckoutSuccess;
use App\Http\Controllers\Controller;
use App\Models\ProviderProfile;
use App\Services\WalletLedgerService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FeaturedController extends Controller
{
    public function __construct(
        private GetFeaturedState $getFeaturedState,
        private PurchaseFeatured $purchaseFeatured,
        private GetActiveProviderProfile $getActiveProviderProfile,
        private WalletLedgerService $walletLedgerService,
        private HandleWooCommerceCheckoutSuccess $handleWooCommerceCheckoutSuccess,
    ) {}

    public function featured(Request $request): View
    {
        $user = Auth::user();
        $profile = $this->getActiveProviderProfile->execute($user);

        // WooCommerce redirects to this page after payment instead of using
        // the return_url we pass. Consume the session UUID and verify the
        // order via the WooCommerce API so credits are applied immediately.
        $sessionUuid = $request->session()->pull('woocommerce_purchase_uuid');
        if ($sessionUuid) {
            $result = $this->handleWooCommerceCheckoutSuccess->execute($sessionUuid, $user);
            $profileName = $result['profile_name'] ?? 'your profile';

            match ($result['status']) {
                'paid' => session()->flash(
                    'checkout_success',
                    "Payment successful! {$result['credits']} credits have been added to {$profileName}."
                ),
                'pending' => session()->flash(
                    'checkout_success',
                    'Your WooCommerce payment is being processed. Credits will appear shortly — please refresh in a moment.'
                ),
                'cancelled', 'refunded' => session()->flash(
                    'checkout_error',
                    'This WooCommerce order was not completed. Please try again or contact support.'
                ),
                default => session()->flash(
                    'checkout_success',
                    'Your WooCommerce payment has been received and is being processed.'
                ),
            };
        }

        $data = $this->getFeaturedState->execute($profile);
        $graphData = $this->buildGraphData($data);

        return view('profile.featured', array_merge($data, $graphData, [
            'userCredits' => $profile ? $this->walletLedgerService->currentBalance($profile) : 0,
            'profile' => $profile,
        ]));
    }

    public function purchase(Request $request): JsonResponse
    {
        $this->authorize('update', ProviderProfile::class);

        $validated = $request->validate([
            'tier' => ['nullable', 'string', 'in:'.implode(',', PurchaseFeatured::TIERS)],
        ]);

        $tier = $validated['tier'] ?? PurchaseFeatured::TIER_NORMAL;

        $user = Auth::user();
        $profile = $this->getActiveProviderProfile->execute($user);

        $result = $this->purchaseFeatured->execute($user, $profile, $tier);

        return response()->json($result->toPayload(), $result->status());
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{graphLabels: array<int, string>, graphCosts: array<int, int>, graphRemainingDays: array<int, int>}
     */
    private function buildGraphData(array $data): array
    {
        $settings = $data['settings'] ?? [];
        $graphTiers = collect([
            [
                'label' => 'Home Page Banner',
                'cost' => (int) ($settings['home_banner_credit_cost'] ?? 0),
                'expires_at' => $data['homeBannerExpiresAt'] ?? null,
            ],
            [
                'label' => 'Home Page Featured',
                'cost' => (int) ($settings['home_featured_credit_cost'] ?? 0),
                'expires_at' => $data['homeFeaturedExpiresAt'] ?? null,
            ],
            [
                'label' => 'Local Banner',
                'cost' => (int) ($settings['local_banner_credit_cost'] ?? 0),
                'expires_at' => $data['localBannerExpiresAt'] ?? null,
            ],
            [
                'label' => 'Featured Badge',
                'cost' => (int) ($settings['normal_featured_credit_cost'] ?? 0),
                'expires_at' => $data['expiresAt'] ?? null,
            ],
        ]);

        return [
            'graphLabels' => $graphTiers->pluck('label')->values()->all(),
            'graphCosts' => $graphTiers->pluck('cost')->values()->all(),
            'graphRemainingDays' => $this->mapRemainingDays($graphTiers),
        ];
    }

    /**
     * @param  Collection<int, array{label: string, cost: int, expires_at: mixed}>  $graphTiers
     * @return array<int, int>
     */
    private function mapRemainingDays(Collection $graphTiers): array
    {
        $now = now();

        return $graphTiers->map(function (array $tier) use ($now): int {
            if (empty($tier['expires_at'])) {
                return 0;
            }

            $expiry = Carbon::parse($tier['expires_at']);

            if ($expiry->lessThanOrEqualTo($now)) {
                return 0;
            }

            return $now->diffInDays($expiry);
        })->values()->all();
    }
}
