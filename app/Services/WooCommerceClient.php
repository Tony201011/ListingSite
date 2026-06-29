<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WooCommerceClient
{
    private string $baseUrl;
    private string $consumerKey;
    private string $consumerSecret;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.woocommerce.base_url'), '/');
        $this->consumerKey = (string) config('services.woocommerce.consumer_key');
        $this->consumerSecret = (string) config('services.woocommerce.consumer_secret');
    }

    public function isConfigured(): bool
    {
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
        return Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
            ->timeout(15)
            ->get($this->baseUrl.$path, $query);
    }
}
