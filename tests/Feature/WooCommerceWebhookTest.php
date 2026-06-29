<?php

namespace Tests\Feature;

use App\Actions\Subscription\InitiateWooCommerceCheckout;
use App\Models\CreditLedgerEntry;
use App\Models\CreditPackage;
use App\Models\CreditPurchase;
use App\Models\ProviderProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class WooCommerceWebhookTest extends TestCase
{
    use RefreshDatabase;

    private string $webhookSecret = 'test-webhook-secret-abc123';

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.woocommerce.webhook_secret', $this->webhookSecret);
        Config::set('services.woocommerce.base_url', 'https://hotadvertising.com.au');
        Config::set('services.woocommerce.checkout_secret', 'test-checkout-secret-xyz');
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function makeSignature(string $payload): string
    {
        return base64_encode(hash_hmac('sha256', $payload, $this->webhookSecret, true));
    }

    private function postWebhook(array $payload, ?string $signature = null): \Illuminate\Testing\TestResponse
    {
        $json = json_encode($payload);
        $sig = $signature ?? $this->makeSignature($json);

        return $this->withHeaders(['X-WC-Webhook-Signature' => $sig])
            ->postJson('/woocommerce/webhook', $payload);
    }

    private function createUser(): User
    {
        return User::factory()->create(['role' => User::ROLE_PROVIDER]);
    }

    private function createProfile(User $user): ProviderProfile
    {
        return ProviderProfile::create([
            'user_id' => $user->id,
            'name' => 'Test Profile',
            'slug' => 'test-profile-'.$user->id,
        ]);
    }

    private function createPackage(array $overrides = []): CreditPackage
    {
        return CreditPackage::create(array_merge([
            'name' => 'Starter',
            'slug' => 'starter',
            'credits' => 50,
            'price' => '19.99',
            'currency' => 'AUD',
            'status' => 'active',
            'woo_product_id' => 101,
        ], $overrides));
    }

    private function createPendingPurchase(User $user, ProviderProfile $profile, CreditPackage $package): CreditPurchase
    {
        return CreditPurchase::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'credit_package_id' => $package->id,
            'credits' => $package->total_credits,
            'amount_cents' => $package->price_cents,
            'currency' => 'AUD',
            'status' => 'pending',
        ]);
    }

    private function buildOrderPayload(CreditPurchase $purchase, array $overrides = []): array
    {
        return array_merge([
            'id' => 9001,
            'status' => 'processing',
            'total' => number_format($purchase->amount_cents / 100, 2, '.', ''),
            'meta_data' => [
                ['key' => '_hotescort_purchase_uuid', 'value' => $purchase->uuid],
            ],
        ], $overrides);
    }

    // ---------------------------------------------------------------
    // Signature verification
    // ---------------------------------------------------------------

    public function test_webhook_rejects_request_with_invalid_signature(): void
    {
        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage();
        $purchase = $this->createPendingPurchase($user, $profile, $package);

        $payload = $this->buildOrderPayload($purchase);
        $response = $this->postWebhook($payload, 'bad-signature');

        $response->assertStatus(401);
        $this->assertEquals('pending', $purchase->fresh()->status);
    }

    public function test_webhook_rejects_when_secret_not_configured(): void
    {
        Config::set('services.woocommerce.webhook_secret', '');

        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage();
        $purchase = $this->createPendingPurchase($user, $profile, $package);

        $payload = $this->buildOrderPayload($purchase);
        $response = $this->postWebhook($payload, $this->makeSignature(json_encode($payload)));

        $response->assertStatus(401);
    }

    // ---------------------------------------------------------------
    // Order status filtering
    // ---------------------------------------------------------------

    public function test_webhook_ignores_non_paid_order_statuses(): void
    {
        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage();
        $purchase = $this->createPendingPurchase($user, $profile, $package);

        foreach (['pending', 'on-hold', 'failed', 'cancelled', 'refunded'] as $status) {
            $payload = $this->buildOrderPayload($purchase, ['id' => 9001, 'status' => $status]);
            $response = $this->postWebhook($payload);

            $response->assertOk();
            $response->assertJsonFragment(['ignored' => true]);
        }

        $this->assertEquals('pending', $purchase->fresh()->status);
    }

    public function test_webhook_accepts_processing_status(): void
    {
        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage();
        $purchase = $this->createPendingPurchase($user, $profile, $package);

        $payload = $this->buildOrderPayload($purchase, ['status' => 'processing']);
        $response = $this->postWebhook($payload);

        $response->assertOk();
        $response->assertJsonFragment(['ok' => true]);
    }

    public function test_webhook_accepts_completed_status(): void
    {
        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage();
        $purchase = $this->createPendingPurchase($user, $profile, $package);

        $payload = $this->buildOrderPayload($purchase, ['status' => 'completed']);
        $response = $this->postWebhook($payload);

        $response->assertOk();
        $response->assertJsonFragment(['ok' => true]);
    }

    // ---------------------------------------------------------------
    // Happy path: credit application
    // ---------------------------------------------------------------

    public function test_webhook_marks_purchase_as_paid_and_creates_ledger_entry(): void
    {
        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage();
        $purchase = $this->createPendingPurchase($user, $profile, $package);

        $payload = $this->buildOrderPayload($purchase, ['id' => 5555]);
        $this->postWebhook($payload)->assertOk();

        $purchase->refresh();
        $this->assertEquals('paid', $purchase->status);
        $this->assertEquals(5555, $purchase->woo_order_id);
        $this->assertNotNull($purchase->paid_at);

        $this->assertDatabaseHas('credit_ledger_entries', [
            'user_id' => $user->id,
            'credit_purchase_id' => $purchase->id,
            'type' => 'purchase',
            'credits_delta' => $package->total_credits,
            'source_type' => 'woocommerce_order',
            'source_id' => '5555',
        ]);
    }

    public function test_webhook_updates_wallet_balance_for_provider_profile(): void
    {
        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage(['credits' => 30]);
        $purchase = $this->createPendingPurchase($user, $profile, $package);

        $payload = $this->buildOrderPayload($purchase);
        $this->postWebhook($payload)->assertOk();

        $profile->refresh();
        $this->assertEquals(30, $profile->credits);
    }

    // ---------------------------------------------------------------
    // Idempotency
    // ---------------------------------------------------------------

    public function test_webhook_is_idempotent_and_does_not_double_credit(): void
    {
        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage(['credits' => 50]);
        $purchase = $this->createPendingPurchase($user, $profile, $package);

        $payload = $this->buildOrderPayload($purchase, ['id' => 7777]);

        // First delivery
        $this->postWebhook($payload)->assertOk();

        // Second delivery (retry)
        $this->postWebhook($payload)->assertOk();

        // Credits applied only once
        $profile->refresh();
        $this->assertEquals(50, $profile->credits);

        $this->assertDatabaseCount('credit_ledger_entries', 1);
    }

    public function test_webhook_ignores_already_paid_purchase(): void
    {
        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage(['credits' => 20]);
        $purchase = $this->createPendingPurchase($user, $profile, $package);
        $purchase->update(['status' => 'paid', 'woo_order_id' => 1111, 'paid_at' => now()]);

        // Manually insert a ledger entry to simulate prior processing
        CreditLedgerEntry::create([
            'user_id' => $user->id,
            'credit_purchase_id' => $purchase->id,
            'type' => 'purchase',
            'credits_delta' => 20,
            'source_type' => 'woocommerce_order',
            'source_id' => '1111',
        ]);

        $profile->update(['credits' => 20]);

        $payload = $this->buildOrderPayload($purchase, ['id' => 1111]);
        $this->postWebhook($payload)->assertOk();

        // No duplicate ledger entries
        $this->assertDatabaseCount('credit_ledger_entries', 1);
        $profile->refresh();
        $this->assertEquals(20, $profile->credits);
    }

    // ---------------------------------------------------------------
    // Edge cases
    // ---------------------------------------------------------------

    public function test_webhook_ignores_order_without_purchase_uuid(): void
    {
        $payload = [
            'id' => 8888,
            'status' => 'processing',
            'total' => '19.99',
            'meta_data' => [],
        ];

        $response = $this->postWebhook($payload);

        $response->assertOk();
        $response->assertJsonFragment(['ignored' => true]);
        $this->assertDatabaseCount('credit_ledger_entries', 0);
    }

    public function test_webhook_ignores_order_with_unknown_purchase_uuid(): void
    {
        $payload = [
            'id' => 8888,
            'status' => 'processing',
            'total' => '19.99',
            'meta_data' => [
                ['key' => '_hotescort_purchase_uuid', 'value' => 'non-existent-uuid'],
            ],
        ];

        $response = $this->postWebhook($payload);

        $response->assertOk();
        $this->assertDatabaseCount('credit_ledger_entries', 0);
    }

    public function test_webhook_does_not_credit_when_amount_mismatches(): void
    {
        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage(['price' => '19.99']);
        $purchase = $this->createPendingPurchase($user, $profile, $package);

        // Send wrong amount (tampered)
        $payload = $this->buildOrderPayload($purchase, ['total' => '9.99']);
        $this->postWebhook($payload)->assertOk();

        $this->assertEquals('pending', $purchase->fresh()->status);
        $this->assertDatabaseCount('credit_ledger_entries', 0);
    }

    // ---------------------------------------------------------------
    // InitiateWooCommerceCheckout action
    // ---------------------------------------------------------------

    public function test_initiate_woo_checkout_creates_pending_purchase_and_returns_url(): void
    {
        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage(['woo_product_id' => 42, 'slug' => 'starter', 'price' => '19.99']);

        $action = app(InitiateWooCommerceCheckout::class);
        $result = $action->execute($user, $package, $profile);

        $this->assertArrayHasKey('checkout_url', $result);
        $this->assertArrayHasKey('purchase', $result);
        $this->assertStringContainsString('hotadvertising.com.au/cart/', $result['checkout_url']);
        $this->assertStringContainsString('add-to-cart=42', $result['checkout_url']);
        $this->assertStringContainsString('package=starter', $result['checkout_url']);

        /** @var CreditPurchase $purchase */
        $purchase = $result['purchase'];
        $this->assertEquals('pending', $purchase->status);
        $this->assertEquals($user->id, $purchase->user_id);
        $this->assertEquals($profile->id, $purchase->provider_profile_id);
        $this->assertEquals($package->price_cents, $purchase->amount_cents);
    }

    public function test_initiate_woo_checkout_returns_error_when_not_configured(): void
    {
        Config::set('services.woocommerce.base_url', '');

        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage();

        $action = app(InitiateWooCommerceCheckout::class);
        $result = $action->execute($user, $package, $profile);

        $this->assertArrayHasKey('error', $result);
    }

    public function test_initiate_woo_checkout_returns_error_when_package_has_no_woo_product(): void
    {
        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage(['woo_product_id' => null]);

        $action = app(InitiateWooCommerceCheckout::class);
        $result = $action->execute($user, $package, $profile);

        $this->assertArrayHasKey('error', $result);
    }

    public function test_checkout_url_includes_valid_hmac_signature(): void
    {
        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage(['slug' => 'starter', 'price' => '19.99']);

        $action = app(InitiateWooCommerceCheckout::class);
        $result = $action->execute($user, $package, $profile);

        $url = $result['checkout_url'];
        parse_str(parse_url($url, PHP_URL_QUERY), $params);

        $valid = InitiateWooCommerceCheckout::verifySignature(
            $params['purchase_uuid'],
            $params['package'],
            $package->price_cents,
            'test-checkout-secret-xyz',
            $params['sig'],
        );

        $this->assertTrue($valid);
    }

    // ---------------------------------------------------------------
    // WooCommerce checkout route
    // ---------------------------------------------------------------

    public function test_woo_checkout_route_redirects_to_woocommerce(): void
    {
        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage(['woo_product_id' => 77]);

        $response = $this->actingAs($user)
            ->withSession(['active_provider_profile_id' => $profile->id])
            ->post('/purchase-credit/woo-checkout', [
                'package_id' => $package->id,
                'provider_profile_id' => $profile->id,
                'invoice_name' => 'Test Invoice',
            ]);

        $response->assertRedirect();
        $this->assertStringContainsString('hotadvertising.com.au', $response->headers->get('Location'));
    }

    // ---------------------------------------------------------------
    // Refunds
    // ---------------------------------------------------------------

    public function test_webhook_reverses_credits_on_refund(): void
    {
        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage(['credits' => 50]);
        $purchase = $this->createPendingPurchase($user, $profile, $package);

        // First: credit the purchase
        $payPayload = $this->buildOrderPayload($purchase, ['id' => 6000, 'status' => 'processing']);
        $this->postWebhook($payPayload)->assertOk();

        $profile->refresh();
        $this->assertEquals(50, $profile->credits);

        // Then: refund it
        $refundPayload = [
            'id' => 6000,
            'status' => 'refunded',
            'total' => number_format($purchase->amount_cents / 100, 2, '.', ''),
            'meta_data' => [
                ['key' => '_hotescort_purchase_uuid', 'value' => $purchase->uuid],
            ],
        ];
        $this->postWebhook($refundPayload)->assertOk();

        $purchase->refresh();
        $this->assertEquals('refunded', $purchase->status);
        $profile->refresh();
        $this->assertEquals(0, $profile->credits);

        $this->assertDatabaseHas('credit_ledger_entries', [
            'source_type' => 'woocommerce_order',
            'source_id' => 'refund:6000',
            'type' => 'refund',
            'credits_delta' => -50,
        ]);
    }

    public function test_webhook_does_not_double_refund(): void
    {
        $user = $this->createUser();
        $profile = $this->createProfile($user);
        $package = $this->createPackage(['credits' => 50]);
        $purchase = $this->createPendingPurchase($user, $profile, $package);

        // Credit, then refund twice
        $payPayload = $this->buildOrderPayload($purchase, ['id' => 7000, 'status' => 'processing']);
        $this->postWebhook($payPayload)->assertOk();

        $refundPayload = [
            'id' => 7000,
            'status' => 'refunded',
            'total' => number_format($purchase->amount_cents / 100, 2, '.', ''),
            'meta_data' => [['key' => '_hotescort_purchase_uuid', 'value' => $purchase->uuid]],
        ];
        $this->postWebhook($refundPayload)->assertOk();
        $this->postWebhook($refundPayload)->assertOk();

        $this->assertDatabaseCount('credit_ledger_entries', 2); // 1 purchase + 1 refund
        $profile->refresh();
        $this->assertEquals(0, $profile->credits);
    }
}
