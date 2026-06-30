<?php

namespace App\Actions\Subscription;

use App\Models\CreditPackage;
use App\Models\CreditPurchase;
use App\Models\ProviderProfile;
use App\Models\PurchaseTransaction;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\URL;

class InitiateWooCommerceCheckout
{
    /**
     * Create a pending CreditPurchase (and linked PurchaseTransaction) and
     * build a signed WooCommerce checkout URL.
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

        // Create a PurchaseTransaction so the purchase appears in purchase history.
        $transaction = PurchaseTransaction::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'provider' => 'woocommerce',
            'provider_checkout_id' => $purchase->uuid,
            'credit_package_id' => $package->id,
            'credits' => (int) $package->credits,
            'bonus_credits' => (int) $package->bonus_credits,
            'amount' => $package->price,
            'currency' => $package->currency ?: 'AUD',
            'status' => 'pending',
            'invoice_name' => $package->name,
            'metadata' => [
                'credit_purchase_uuid' => $purchase->uuid,
                'package_name' => $package->name,
                'base_credits' => (int) $package->credits,
                'bonus_credits' => (int) $package->bonus_credits,
            ],
        ]);

        $purchase->update(['purchase_transaction_id' => $transaction->id]);

        $signature = $this->sign($purchase->uuid, $package->slug, $purchase->amount_cents, $checkoutSecret);
        $returnUrl = URL::route('purchase-credit.success', [
            'provider' => 'woocommerce',
            'purchase_uuid' => $purchase->uuid,
        ]);

        $checkoutUrl = $baseUrl.'/cart/?'.http_build_query([
            'add-to-cart' => $package->woo_product_id,
            'purchase_uuid' => $purchase->uuid,
            'package' => $package->slug,
            'sig' => $signature,
            'return_url' => $returnUrl,
            'redirect_to' => $returnUrl,
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
