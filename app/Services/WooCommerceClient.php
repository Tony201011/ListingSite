<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WooCommerceClient
{
    private ?string $baseUrl = null;
    private ?string $consumerKey = null;
    private ?string $consumerSecret = null;
    private bool $initialized = false;

    public function __construct() {}

    private function initialize(): void
    {
        if ($this->initialized) {
            return;
        }

        $setting = SiteSetting::query()->first();

        $this->baseUrl = rtrim(
            (string) ($setting?->woocommerce_base_url ?: config('services.woocommerce.base_url')),
            '/'
        );
        $this->consumerKey = (string) ($setting?->woocommerce_consumer_key ?: config('services.woocommerce.consumer_key'));
        $this->consumerSecret = (string) ($setting?->woocommerce_consumer_secret ?: config('services.woocommerce.consumer_secret'));
        $this->initialized = true;
    }

    public function isConfigured(): bool
    {
        $this->initialize();

        return $this->baseUrl && $this->consumerKey && $this->consumerSecret;
    }

    /**
     * Fetch recent paid orders from WooCommerce REST API.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getPaidOrders(int $page = 1, int $perPage = 50, string $after = ''): array
    {
        $params = [
            'status' => 'processing,completed',
            'per_page' => $perPage,
            'page' => $page,
            'orderby' => 'date',
            'order' => 'desc',
        ];

        if ($after) {
            $params['after'] = $after;
        }

        $response = $this->get('/wp-json/wc/v3/orders', $params);

        if (! $response->successful()) {
            Log::error('WooCommerce API: failed to fetch orders', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [];
        }

        return $response->json() ?? [];
    }

    /**
     * Fetch a single order by WooCommerce order ID.
     *
     * @return array<string, mixed>|null
     */
    public function getOrder(int $orderId): ?array
    {
        $response = $this->get("/wp-json/wc/v3/orders/{$orderId}");

        if (! $response->successful()) {
            return null;
        }

        return $response->json();
    }

    private function get(string $path, array $query = []): Response
    {
        $this->initialize();

        return Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->timeout(15)
            ->get($this->baseUrl.$path, $query);
    }
}
