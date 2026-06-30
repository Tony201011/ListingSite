<?php

namespace Tests\Feature\Subscription;

use App\Actions\Subscription\HandleWooCommerceCheckoutSuccess;
use App\Models\CreditPackage;
use App\Models\CreditPurchase;
use App\Models\ProviderProfile;
use App\Models\User;
use App\Services\WooCommerceClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class HandleWooCommerceCheckoutSuccessTest extends TestCase
{
    use RefreshDatabase;

    private function createPurchase(string $status = 'pending', int $credits = 30): array
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Test Provider',
            'slug' => 'test-provider-'.$user->id,
        ]);
        $package = CreditPackage::query()->create([
            'name' => 'Starter',
            'slug' => 'starter',
            'credits' => $credits,
            'price' => '9.99',
            'currency' => 'AUD',
            'status' => 'active',
            'woo_product_id' => 42,
        ]);
        $purchase = CreditPurchase::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'credit_package_id' => $package->id,
            'credits' => $credits,
            'amount_cents' => 999,
            'currency' => 'AUD',
            'status' => $status,
            'paid_at' => $status === 'paid' ? now() : null,
        ]);

        return compact('user', 'profile', 'purchase');
    }

    public function test_returns_paid_when_purchase_is_already_paid(): void
    {
        ['user' => $user, 'purchase' => $purchase] = $this->createPurchase('paid');

        $action = app(HandleWooCommerceCheckoutSuccess::class);
        $result = $action->execute($purchase->uuid, $user);

        $this->assertEquals('paid', $result['status']);
        $this->assertEquals(30, $result['credits']);
    }

    public function test_returns_not_found_for_missing_uuid(): void
    {
        $user = User::factory()->create();

        $action = app(HandleWooCommerceCheckoutSuccess::class);
        $result = $action->execute('non-existent-uuid', $user);

        $this->assertEquals('not_found', $result['status']);
    }

    public function test_returns_not_found_when_purchase_belongs_to_different_user(): void
    {
        ['purchase' => $purchase] = $this->createPurchase('pending');
        $otherUser = User::factory()->create();

        $action = app(HandleWooCommerceCheckoutSuccess::class);
        $result = $action->execute($purchase->uuid, $otherUser);

        $this->assertEquals('not_found', $result['status']);
    }

    public function test_returns_pending_when_woocommerce_not_configured(): void
    {
        ['user' => $user, 'purchase' => $purchase] = $this->createPurchase('pending');

        // WooCommerceClient::isConfigured() returns false when no config is set (default in tests)
        $action = app(HandleWooCommerceCheckoutSuccess::class);
        $result = $action->execute($purchase->uuid, $user);

        $this->assertEquals('pending', $result['status']);
    }

    public function test_applies_credits_via_api_when_purchase_is_pending_and_order_is_paid(): void
    {
        ['user' => $user, 'profile' => $profile, 'purchase' => $purchase] = $this->createPurchase('pending', 50);

        $orderPayload = [
            'id' => 9999,
            'status' => 'processing',
            'total' => '9.99',
            'currency' => 'AUD',
            'meta_data' => [
                ['key' => '_hotescort_purchase_uuid', 'value' => $purchase->uuid],
            ],
        ];

        $mockClient = Mockery::mock(WooCommerceClient::class);
        $mockClient->shouldReceive('isConfigured')->andReturn(true);
        $mockClient->shouldReceive('findOrderByPurchaseUuid')
            ->with($purchase->uuid)
            ->andReturn($orderPayload);

        $this->app->instance(WooCommerceClient::class, $mockClient);

        $action = app(HandleWooCommerceCheckoutSuccess::class);
        $result = $action->execute($purchase->uuid, $user);

        $this->assertEquals('paid', $result['status']);
        $this->assertEquals(50, $result['credits']);

        $purchase->refresh();
        $this->assertEquals('paid', $purchase->status);
        $this->assertEquals(9999, $purchase->woo_order_id);

        $profile->refresh();
        $this->assertEquals(50, $profile->credits);
    }

    public function test_returns_pending_when_api_order_not_found(): void
    {
        ['user' => $user, 'purchase' => $purchase] = $this->createPurchase('pending');

        $mockClient = Mockery::mock(WooCommerceClient::class);
        $mockClient->shouldReceive('isConfigured')->andReturn(true);
        $mockClient->shouldReceive('findOrderByPurchaseUuid')
            ->with($purchase->uuid)
            ->andReturn(null);

        $this->app->instance(WooCommerceClient::class, $mockClient);

        $action = app(HandleWooCommerceCheckoutSuccess::class);
        $result = $action->execute($purchase->uuid, $user);

        $this->assertEquals('pending', $result['status']);

        $purchase->refresh();
        $this->assertEquals('pending', $purchase->status);
    }

    public function test_returns_pending_when_api_order_not_yet_paid(): void
    {
        ['user' => $user, 'purchase' => $purchase] = $this->createPurchase('pending');

        $mockClient = Mockery::mock(WooCommerceClient::class);
        $mockClient->shouldReceive('isConfigured')->andReturn(true);
        $mockClient->shouldReceive('findOrderByPurchaseUuid')
            ->with($purchase->uuid)
            ->andReturn(['id' => 9999, 'status' => 'pending', 'total' => '9.99']);

        $this->app->instance(WooCommerceClient::class, $mockClient);

        $action = app(HandleWooCommerceCheckoutSuccess::class);
        $result = $action->execute($purchase->uuid, $user);

        $this->assertEquals('pending', $result['status']);

        $purchase->refresh();
        $this->assertEquals('pending', $purchase->status);
    }

    public function test_returns_cancelled_status_for_cancelled_purchase(): void
    {
        ['user' => $user, 'purchase' => $purchase] = $this->createPurchase('cancelled');

        $action = app(HandleWooCommerceCheckoutSuccess::class);
        $result = $action->execute($purchase->uuid, $user);

        $this->assertEquals('cancelled', $result['status']);
    }

    public function test_null_user_allows_access_to_any_purchase(): void
    {
        ['purchase' => $purchase] = $this->createPurchase('paid');

        $action = app(HandleWooCommerceCheckoutSuccess::class);
        $result = $action->execute($purchase->uuid, null);

        $this->assertEquals('paid', $result['status']);
    }
}
