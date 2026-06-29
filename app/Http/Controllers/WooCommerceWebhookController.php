<?php

namespace App\Http\Controllers;

use App\Models\CreditLedgerEntry;
use App\Models\CreditPurchase;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use App\Services\WalletLedgerService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

final class WooCommerceWebhookController extends Controller
{
    public function __construct(
        private WalletLedgerService $walletLedgerService,
    ) {}

    public function handle(Request $request): JsonResponse
    {
        $payload = $request->getContent();

        if (! $this->verifySignature($request, $payload)) {
            Log::warning('WooCommerce webhook: invalid signature');

            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $order = $request->json()->all();

        $status = $order['status'] ?? null;

        if (in_array($status, ['processing', 'completed'], true)) {
            $purchaseUuid = $this->extractPurchaseUuid($order);

            if (! $purchaseUuid) {
                Log::info('WooCommerce webhook: order has no purchase UUID, ignoring', [
                    'order_id' => $order['id'] ?? null,
                ]);

                return response()->json(['ignored' => true]);
            }

            $this->processOrder($order, $purchaseUuid);

            return response()->json(['ok' => true]);
        }

        if (in_array($status, ['refunded', 'cancelled'], true)) {
            $purchaseUuid = $this->extractPurchaseUuid($order);

            if ($purchaseUuid) {
                $this->processRefund($order, $purchaseUuid);
            }

            return response()->json(['ok' => true]);
        }

        return response()->json(['ignored' => true, 'status' => $status]);
    }

    private function verifySignature(Request $request, string $payload): bool
    {
        $setting = SiteSetting::query()->first();
        $secret = (string) ($setting?->woocommerce_webhook_secret ?: config('services.woocommerce.webhook_secret'));

        if (! $secret) {
            Log::error('WooCommerce webhook: webhook_secret is not configured');

            return false;
        }

        $expected = base64_encode(hash_hmac('sha256', $payload, $secret, true));
        $actual = (string) $request->header('X-WC-Webhook-Signature', '');

        return hash_equals($expected, $actual);
    }

    private function extractPurchaseUuid(array $order): ?string
    {
        $meta = $order['meta_data'] ?? [];

        foreach ($meta as $item) {
            if (($item['key'] ?? '') === '_hotescort_purchase_uuid') {
                return $item['value'] ?? null;
            }
        }

        return null;
    }

    private function processOrder(array $order, string $purchaseUuid): void
    {
        DB::transaction(function () use ($order, $purchaseUuid): void {
            /** @var CreditPurchase|null $purchase */
            $purchase = CreditPurchase::where('uuid', $purchaseUuid)
                ->lockForUpdate()
                ->first();

            if (! $purchase) {
                Log::warning('WooCommerce webhook: purchase UUID not found', [
                    'purchase_uuid' => $purchaseUuid,
                    'order_id' => $order['id'] ?? null,
                ]);

                return;
            }

            if ($purchase->status === 'paid') {
                Log::info('WooCommerce webhook: purchase already paid, ignoring', [
                    'purchase_uuid' => $purchaseUuid,
                    'order_id' => $order['id'] ?? null,
                ]);

                return;
            }

            $orderAmountCents = $this->parseAmountCents((string) ($order['total'] ?? '0'));

            if ($purchase->amount_cents !== $orderAmountCents) {
                Log::error('WooCommerce webhook: order amount mismatch', [
                    'purchase_uuid' => $purchaseUuid,
                    'expected_cents' => $purchase->amount_cents,
                    'received_cents' => $orderAmountCents,
                    'order_id' => $order['id'] ?? null,
                ]);

                return;
            }

            // Idempotency guard: skip if ledger entry already exists for this order.
            $sourceId = (string) ($order['id'] ?? '');
            $alreadyCredited = CreditLedgerEntry::where('source_type', 'woocommerce_order')
                ->where('source_id', $sourceId)
                ->exists();

            if ($alreadyCredited) {
                Log::info('WooCommerce webhook: ledger entry already exists, ignoring', [
                    'order_id' => $order['id'] ?? null,
                ]);

                return;
            }

            $purchase->update([
                'status' => 'paid',
                'woo_order_id' => (int) ($order['id'] ?? 0),
                'paid_at' => now(),
            ]);

            CreditLedgerEntry::create([
                'user_id' => $purchase->user_id,
                'credit_purchase_id' => $purchase->id,
                'type' => 'purchase',
                'credits_delta' => $purchase->credits,
                'source_type' => 'woocommerce_order',
                'source_id' => $sourceId,
                'description' => "Purchased {$purchase->credits} advertising credits via WooCommerce order #{$order['id']}",
            ]);

            $profile = ProviderProfile::withTrashed()->find($purchase->provider_profile_id);

            if ($profile instanceof ProviderProfile) {
                $this->walletLedgerService->record(
                    $profile,
                    $purchase->credits,
                    'purchase_credit',
                    "Purchased {$purchase->credits} advertising credits via WooCommerce order #{$order['id']}",
                    null,
                    'purchase',
                );
            }

            Log::info('WooCommerce webhook: credits applied', [
                'user_id' => $purchase->user_id,
                'provider_profile_id' => $purchase->provider_profile_id,
                'credits' => $purchase->credits,
                'order_id' => $order['id'] ?? null,
                'purchase_uuid' => $purchaseUuid,
            ]);
        });
    }

    private function processRefund(array $order, string $purchaseUuid): void
    {
        DB::transaction(function () use ($order, $purchaseUuid): void {
            /** @var CreditPurchase|null $purchase */
            $purchase = CreditPurchase::where('uuid', $purchaseUuid)
                ->lockForUpdate()
                ->first();

            if (! $purchase) {
                return;
            }

            if ($purchase->status === 'refunded') {
                return;
            }

            // Only reverse credits if the purchase was actually paid.
            if ($purchase->status !== 'paid') {
                $purchase->update(['status' => 'cancelled']);

                return;
            }

            $sourceId = 'refund:'.($order['id'] ?? '');
            $alreadyRefunded = CreditLedgerEntry::where('source_type', 'woocommerce_order')
                ->where('source_id', $sourceId)
                ->exists();

            if ($alreadyRefunded) {
                return;
            }

            $purchase->update(['status' => 'refunded']);

            CreditLedgerEntry::create([
                'user_id' => $purchase->user_id,
                'credit_purchase_id' => $purchase->id,
                'type' => 'refund',
                'credits_delta' => -$purchase->credits,
                'source_type' => 'woocommerce_order',
                'source_id' => $sourceId,
                'description' => "Refund: -{$purchase->credits} advertising credits for WooCommerce order #{$order['id']}",
            ]);

            $profile = ProviderProfile::withTrashed()->find($purchase->provider_profile_id);

            if ($profile instanceof ProviderProfile) {
                $this->walletLedgerService->record(
                    $profile,
                    -$purchase->credits,
                    'refund',
                    "Refund: -{$purchase->credits} advertising credits for WooCommerce order #{$order['id']}",
                    null,
                    'refund',
                );
            }

            Log::info('WooCommerce webhook: credits reversed for refund', [
                'user_id' => $purchase->user_id,
                'provider_profile_id' => $purchase->provider_profile_id,
                'credits' => -$purchase->credits,
                'order_id' => $order['id'] ?? null,
            ]);
        });
    }

    private function parseAmountCents(string $total): int
    {
        return (int) round((float) $total * 100);
    }
}
