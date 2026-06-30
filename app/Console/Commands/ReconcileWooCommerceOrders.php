<?php

namespace App\Console\Commands;

use App\Models\CreditLedgerEntry;
use App\Models\CreditPurchase;
use App\Models\ProviderProfile;
use App\Services\WalletLedgerService;
use App\Services\WooCommerceClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReconcileWooCommerceOrders extends Command
{
    protected $signature = 'woocommerce:reconcile
                            {--hours=1 : Look back this many hours for recent orders}
                            {--dry-run : Report what would be credited without applying changes}';

    protected $description = 'Reconcile WooCommerce paid orders and credit any missing purchases.';

    public function __construct(
        private WooCommerceClient $wooCommerceClient,
        private WalletLedgerService $walletLedgerService,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        if (! $this->wooCommerceClient->isConfigured()) {
            $this->warn('WooCommerce client is not configured. Set WOOCOMMERCE_BASE_URL, WOOCOMMERCE_CONSUMER_KEY, and WOOCOMMERCE_CONSUMER_SECRET.');

            return self::FAILURE;
        }

        $hours = (int) $this->option('hours');
        $dryRun = (bool) $this->option('dry-run');
        $after = now()->subHours($hours)->toIso8601String();

        $this->info("Fetching WooCommerce paid orders from the last {$hours} hour(s)...");

        $credited = 0;
        $skipped = 0;
        $page = 1;

        do {
            $orders = $this->wooCommerceClient->getPaidOrders($page, 50, $after);

            if (empty($orders)) {
                break;
            }

            foreach ($orders as $order) {
                $result = $this->processOrder($order, $dryRun);

                match ($result) {
                    'credited' => $credited++,
                    default => $skipped++,
                };
            }

            $page++;
        } while (count($orders) === 50);

        $label = $dryRun ? '[DRY RUN] Would credit' : 'Credited';
        $this->info("{$label}: {$credited} order(s). Skipped: {$skipped} order(s).");

        return self::SUCCESS;
    }

    private function processOrder(array $order, bool $dryRun): string
    {
        $orderId = (int) ($order['id'] ?? 0);
        $status = $order['status'] ?? '';

        if (! in_array($status, ['processing', 'completed'], true)) {
            return 'skipped';
        }

        $purchaseUuid = $this->extractPurchaseUuid($order);

        if (! $purchaseUuid) {
            return 'skipped';
        }

        $alreadyCredited = CreditLedgerEntry::where('source_type', 'woocommerce_order')
            ->where('source_id', (string) $orderId)
            ->exists();

        if ($alreadyCredited) {
            return 'skipped';
        }

        if ($dryRun) {
            $this->line("[DRY RUN] Would credit order #{$orderId} for purchase UUID {$purchaseUuid}");

            return 'credited';
        }

        DB::transaction(function () use ($orderId, $purchaseUuid, $order): void {
            /** @var CreditPurchase|null $purchase */
            $purchase = CreditPurchase::where('uuid', $purchaseUuid)
                ->lockForUpdate()
                ->first();

            if (! $purchase || $purchase->status === 'paid') {
                return;
            }

            $orderAmountCents = $this->parseAmountCents((string) ($order['total'] ?? '0'));
            $orderCurrency = (string) ($order['currency'] ?? '');
            if ($orderCurrency === '') {
                $orderCurrency = $purchase->currency;
            }

            $purchase->update([
                'status' => 'paid',
                'woo_order_id' => $orderId,
                'amount_cents' => $orderAmountCents,
                'currency' => $orderCurrency,
                'paid_at' => now(),
            ]);

            CreditLedgerEntry::create([
                'user_id' => $purchase->user_id,
                'credit_purchase_id' => $purchase->id,
                'type' => 'purchase',
                'credits_delta' => $purchase->credits,
                'source_type' => 'woocommerce_order',
                'source_id' => (string) $orderId,
                'description' => "Reconciled: {$purchase->credits} advertising credits from WooCommerce order #{$orderId}",
            ]);

            $profile = ProviderProfile::withTrashed()->find($purchase->provider_profile_id);

            if ($profile instanceof ProviderProfile) {
                $this->walletLedgerService->record(
                    $profile,
                    $purchase->credits,
                    'purchase_credit',
                    "Reconciled: {$purchase->credits} advertising credits from WooCommerce order #{$orderId}",
                    null,
                    'purchase',
                );
            }

            Log::info('WooCommerce reconciliation: credits applied', [
                'order_id' => $orderId,
                'purchase_uuid' => $purchaseUuid,
                'user_id' => $purchase->user_id,
                'credits' => $purchase->credits,
            ]);
        });

        return 'credited';
    }

    /**
     * @param  array<string, mixed>  $order
     */
    private function extractPurchaseUuid(array $order): ?string
    {
        foreach ($order['meta_data'] ?? [] as $item) {
            $purchaseUuid = $this->extractPurchaseUuidFromMetaItem($item);
            if ($purchaseUuid) {
                return $purchaseUuid;
            }
        }

        foreach ($order['line_items'] ?? [] as $lineItem) {
            foreach (($lineItem['meta_data'] ?? []) as $item) {
                $purchaseUuid = $this->extractPurchaseUuidFromMetaItem($item);
                if ($purchaseUuid) {
                    return $purchaseUuid;
                }
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $metaItem
     */
    private function extractPurchaseUuidFromMetaItem(array $metaItem): ?string
    {
        $key = (string) ($metaItem['key'] ?? '');

        if (! in_array($key, ['_hotescort_purchase_uuid', 'hotescort_purchase_uuid', 'purchase_uuid'], true)) {
            return null;
        }

        $value = $metaItem['value'] ?? null;

        if (! is_string($value) || $value === '') {
            return null;
        }

        return $value;
    }

    private function parseAmountCents(string $total): int
    {
        return (int) round((float) $total * 100);
    }
}
