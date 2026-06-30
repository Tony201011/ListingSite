<?php

namespace App\Actions\Subscription;

use App\Models\CreditPurchase;
use App\Models\User;
use App\Services\WooCommerceClient;
use Illuminate\Support\Facades\Log;

class HandleWooCommerceCheckoutSuccess
{
    public function __construct(
        private WooCommerceClient $wooCommerceClient,
        private ProcessWooCommerceOrder $processWooCommerceOrder,
    ) {}

    /**
     * Handle the return from a WooCommerce checkout.
     *
     * When the purchase is still pending, the method attempts to look up the
     * corresponding WooCommerce order via the REST API and immediately apply
     * credits, so that users are not left waiting for a webhook that may be
     * delayed or misconfigured.
     *
     * @return array{status: string, credits?: int, profile_name?: string}
     */
    public function execute(string $purchaseUuid, ?User $user): array
    {
        /** @var CreditPurchase|null $purchase */
        $purchase = CreditPurchase::query()
            ->with('providerProfile')
            ->where('uuid', $purchaseUuid)
            ->first();

        if (! $purchase) {
            return ['status' => 'not_found'];
        }

        if ($user && $purchase->user_id !== $user->id) {
            return ['status' => 'not_found'];
        }

        $profileName = $purchase->providerProfile?->name ?? 'selected profile';

        if ($purchase->status === 'paid') {
            return ['status' => 'paid', 'credits' => $purchase->credits, 'profile_name' => $profileName];
        }

        if (in_array($purchase->status, ['cancelled', 'refunded'], true)) {
            return ['status' => $purchase->status, 'profile_name' => $profileName];
        }

        // Purchase is still pending; try to verify payment via the WooCommerce
        // API and apply credits immediately, rather than waiting for the webhook.
        if ($this->wooCommerceClient->isConfigured()) {
            $order = $this->wooCommerceClient->findOrderByPurchaseUuid($purchaseUuid);

            if ($order && in_array($order['status'] ?? '', ['processing', 'completed'], true)) {
                $result = $this->processWooCommerceOrder->execute($order, $purchaseUuid);

                if (in_array($result, ['credited', 'already_paid'], true)) {
                    $purchase->refresh();
                    $profileName = $purchase->providerProfile?->name ?? $profileName;

                    return ['status' => 'paid', 'credits' => $purchase->credits, 'profile_name' => $profileName];
                }
            } else {
                Log::info('HandleWooCommerceCheckoutSuccess: order not found or not paid via API, awaiting webhook', [
                    'purchase_uuid' => $purchaseUuid,
                    'order_status' => $order['status'] ?? null,
                ]);
            }
        }

        return ['status' => 'pending', 'profile_name' => $profileName];
    }
}
