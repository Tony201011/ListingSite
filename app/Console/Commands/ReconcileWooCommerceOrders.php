<?php

namespace App\Console\Commands;

use App\Actions\Subscription\ProcessWooCommerceOrder;
use App\Models\CreditLedgerEntry;
use App\Services\WooCommerceClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReconcileWooCommerceOrders extends Command
{
    protected $signature = 'woocommerce:reconcile
                            {--hours=1 : Look back this many hours for recent orders}
                            {--dry-run : Report what would be credited without applying changes}';

    protected $description = 'Reconcile WooCommerce paid orders and credit any missing purchases.';

    public function __construct(
        private WooCommerceClient $wooCommerceClient,
        private ProcessWooCommerceOrder $processWooCommerceOrder,
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

        $result = $this->processWooCommerceOrder->execute($order, $purchaseUuid);

        if ($result === 'credited') {
            return 'credited';
        }

        Log::info('WooCommerce reconciliation: skipped order', [
            'order_id' => $orderId,
            'purchase_uuid' => $purchaseUuid,
            'result' => $result,
        ]);

        return 'skipped';
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
}

