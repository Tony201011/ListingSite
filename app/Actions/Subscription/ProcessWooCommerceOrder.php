<?php

namespace App\Actions\Subscription;

use App\Models\CreditLedgerEntry;
use App\Models\CreditPurchase;
use App\Models\ProviderProfile;
use App\Models\PurchaseTransaction;
use App\Services\WalletLedgerService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessWooCommerceOrder
{
    public function __construct(
        private WalletLedgerService $walletLedgerService,
    ) {}

    /**
     * Process a paid WooCommerce order and credit the matching CreditPurchase.
     *
     * @param  array<string, mixed>  $order  WooCommerce order payload
     * @return 'credited'|'already_paid'|'not_found'|'skipped'
     */
    public function execute(array $order, string $purchaseUuid): string
    {
        return DB::transaction(function () use ($order, $purchaseUuid): string {
            /** @var CreditPurchase|null $purchase */
            $purchase = CreditPurchase::where('uuid', $purchaseUuid)
                ->lockForUpdate()
                ->first();

            if (! $purchase) {
                Log::warning('ProcessWooCommerceOrder: purchase UUID not found', [
                    'purchase_uuid' => $purchaseUuid,
                    'order_id' => $order['id'] ?? null,
                ]);

                return 'not_found';
            }

            if ($purchase->status === 'paid') {
                return 'already_paid';
            }

            $sourceId = (string) ($order['id'] ?? '');

            // Idempotency guard: skip if ledger entry already exists for this order.
            if ($sourceId !== '' && CreditLedgerEntry::where('source_type', 'woocommerce_order')
                ->where('source_id', $sourceId)
                ->exists()) {
                return 'already_paid';
            }

            $orderAmountCents = $this->parseAmountCents((string) ($order['total'] ?? '0'));

            if ($orderAmountCents !== $purchase->amount_cents) {
                Log::warning('ProcessWooCommerceOrder: syncing mismatched order amount', [
                    'purchase_uuid' => $purchaseUuid,
                    'stored_cents' => $purchase->amount_cents,
                    'received_cents' => $orderAmountCents,
                    'order_id' => $order['id'] ?? null,
                ]);
            }

            $orderCurrency = (string) ($order['currency'] ?? '');
            if ($orderCurrency === '') {
                $orderCurrency = $purchase->currency;
            }

            $wooOrderId = $sourceId !== '' ? (int) $sourceId : null;

            $purchase->update([
                'status' => 'paid',
                'woo_order_id' => $wooOrderId,
                'amount_cents' => $orderAmountCents,
                'currency' => $orderCurrency,
                'paid_at' => now(),
            ]);

            // Sync the linked PurchaseTransaction so it appears in purchase history.
            $this->syncPurchaseTransaction($purchase, $wooOrderId, $orderAmountCents, $orderCurrency);

            if ($sourceId !== '') {
                $orderLabel = "WooCommerce order #{$sourceId}";

                CreditLedgerEntry::create([
                    'user_id' => $purchase->user_id,
                    'credit_purchase_id' => $purchase->id,
                    'type' => 'purchase',
                    'credits_delta' => $purchase->credits,
                    'source_type' => 'woocommerce_order',
                    'source_id' => $sourceId,
                    'description' => "Purchased {$purchase->credits} advertising credits via {$orderLabel}",
                ]);
            }

            $profile = ProviderProfile::withTrashed()->find($purchase->provider_profile_id);

            if ($profile instanceof ProviderProfile) {
                $orderLabel = $sourceId !== '' ? "WooCommerce order #{$sourceId}" : 'WooCommerce';

                $this->walletLedgerService->record(
                    $profile,
                    $purchase->credits,
                    'purchase_credit',
                    "Purchased {$purchase->credits} advertising credits via {$orderLabel}",
                    null,
                    'purchase',
                );
            }

            Log::info('ProcessWooCommerceOrder: credits applied', [
                'user_id' => $purchase->user_id,
                'provider_profile_id' => $purchase->provider_profile_id,
                'credits' => $purchase->credits,
                'order_id' => $order['id'] ?? null,
                'purchase_uuid' => $purchaseUuid,
            ]);

            return 'credited';
        });
    }

    /**
     * Mark the linked PurchaseTransaction as paid so it shows in purchase history.
     * If no linked transaction exists (e.g. orders created before this fix), create one.
     */
    private function syncPurchaseTransaction(CreditPurchase $purchase, ?int $wooOrderId, int $orderAmountCents, string $currency): void
    {
        $transaction = $purchase->purchase_transaction_id
            ? PurchaseTransaction::find($purchase->purchase_transaction_id)
            : PurchaseTransaction::where('provider', 'woocommerce')
                ->where('provider_checkout_id', $purchase->uuid)
                ->first();

        if (! $transaction) {
            // No linked transaction — create one so the purchase appears in history.
            $transaction = PurchaseTransaction::create([
                'user_id' => $purchase->user_id,
                'provider_profile_id' => $purchase->provider_profile_id,
                'provider' => 'woocommerce',
                'provider_checkout_id' => $purchase->uuid,
                'credit_package_id' => $purchase->credit_package_id,
                'credits' => $purchase->credits,
                'bonus_credits' => 0,
                'amount' => $orderAmountCents / 100,
                'currency' => $currency,
                'status' => 'pending',
                'invoice_name' => $purchase->package?->name,
                'metadata' => [
                    'credit_purchase_uuid' => $purchase->uuid,
                ],
                'paid_at' => now(),
            ]);

            $purchase->update(['purchase_transaction_id' => $transaction->id]);
        }

        if ($transaction->status !== 'paid') {
            $transaction->update([
                'status' => 'paid',
                'provider_transaction_id' => $wooOrderId ? (string) $wooOrderId : null,
                'amount' => $orderAmountCents / 100,
                'currency' => $currency,
                'paid_at' => now(),
            ]);
        }
    }

    private function parseAmountCents(string $total): int
    {
        return (int) round((float) $total * 100);
    }
}
