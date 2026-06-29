<?php

namespace App\Actions\Subscription;

use App\Models\CreditPackage;
use App\Models\CreditPurchase;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class InitiateWooCommerceCheckout
{
    /**
     * Create a pending CreditPurchase and build a signed WooCommerce checkout URL.
     *
     * @return array{checkout_url: string, purchase: CreditPurchase}|array{error: string}
     */
    public function execute(User $user, CreditPackage $package, ProviderProfile $profile): array
    {
        $setting = SiteSetting::query()->first();

        $baseUrl = rtrim(
            (string) ($setting?->woocommerce_base_url ?: config('services.woocommerce.base_url')),
            '/'
        );
        $checkoutSecret = (string) ($setting?->woocommerce_checkout_secret ?: config('services.woocommerce.checkout_secret'));

        if (! $baseUrl || ! $checkoutSecret) {
            return ['error' => 'WooCommerce checkout is not configured.'];
        }

        if (! $package->hasWooProduct()) {
            return ['error' => 'This credit package is not available for WooCommerce checkout.'];
        }

        $purchase = CreditPurchase::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'credit_package_id' => $package->id,
            'credits' => $package->total_credits,
            'amount_cents' => $package->price_cents,
            'currency' => $package->currency ?: 'AUD',
            'status' => 'pending',
        ]);

        $signature = $this->sign($purchase->uuid, $package->slug, $purchase->amount_cents, $checkoutSecret);

        $checkoutUrl = $baseUrl.'/checkout/?'.http_build_query([
            'add-to-cart' => $package->woo_product_id,
            'purchase_uuid' => $purchase->uuid,
            'package' => $package->slug,
            'sig' => $signature,
        ]);

        return [
            'checkout_url' => $checkoutUrl,
            'purchase' => $purchase,
        ];
    }

    public static function sign(string $uuid, string $packageSlug, int $amountCents, string $secret): string
    {
        $payload = implode(':', [$uuid, $packageSlug, (string) $amountCents]);

        return base64_encode(hash_hmac('sha256', $payload, $secret, true));
    }

    public static function verifySignature(
        string $uuid,
        string $packageSlug,
        int $amountCents,
        string $secret,
        string $signature,
    ): bool {
        $expected = self::sign($uuid, $packageSlug, $amountCents, $secret);

        return hash_equals($expected, $signature);
    }
}
