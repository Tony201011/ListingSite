<?php

namespace Tests\Feature\Subscription;

use App\Actions\Referral\ProcessReferralRewardForFirstPayment;
use App\Actions\Subscription\HandleStripeWebhook;
use App\Actions\Subscription\SendCreditPurchaseEmail;
use App\Http\Middleware\CheckProfileSteps;
use App\Http\Middleware\EnsureProfileSelected;
use App\Models\CreditPackage;
use App\Models\ProviderProfile;
use App\Models\PurchaseTransaction;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PurchaseCreditFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([CheckProfileSteps::class, EnsureProfileSelected::class]);
    }

    // ---------------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------------

    private function createProvider(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
        ]);

        return $user;
    }

    private function actingAsProvider(User $user): static
    {
        $profile = $user->providerProfile;

        return $this->actingAs($user)->withSession([
            'active_provider_profile_id' => $profile?->id,
        ]);
    }

    private function createActivePackage(array $overrides = []): CreditPackage
    {
        return CreditPackage::create(array_merge([
            'name' => 'Starter Pack',
            'credits' => 30,
            'price' => '9.99',
            'status' => 'active',
            'sort_order' => 1,
        ], $overrides));
    }

    // ---------------------------------------------------------------
    // Purchase Credit page
    // ---------------------------------------------------------------

    public function test_purchase_credit_page_is_accessible_for_provider(): void
    {
        $user = $this->createProvider();
        $this->createActivePackage();

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertViewIs('subscription.purchase-credit');
    }

    public function test_purchase_credit_page_targets_its_own_alpine_component_for_payment_flow(): void
    {
        $user = $this->createProvider();
        $this->createActivePackage();
        SiteSetting::query()->create([
            'stripe_enabled' => true,
            'stripe_publishable_key' => 'pk_test_123',
            'stripe_secret_key' => 'sk_test_123',
        ]);

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertSee('id="purchase-credit-flow"', false);
        $response->assertSee("document.getElementById('purchase-credit-flow')", false);
    }

    public function test_purchase_credit_page_shows_only_active_packages(): void
    {
        $user = $this->createProvider();
        $active = $this->createActivePackage(['name' => 'Active Pack', 'sort_order' => 1]);
        $this->createActivePackage(['name' => 'Inactive Pack', 'status' => 'inactive', 'sort_order' => 2]);

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertViewHas('packages', function ($packages) use ($active) {
            return $packages->count() === 1 && $packages->first()->id === $active->id;
        });
    }

    public function test_purchase_credit_page_requires_authentication(): void
    {
        $response = $this->get('/purchase-credit');

        $response->assertRedirect('/signin');
    }

    public function test_purchase_credit_page_shows_user_current_balance(): void
    {
        $user = $this->createProvider();
        $user->providerProfile->update(['credits' => 42]);

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertViewHas('currentBalance', 42);
    }

    public function test_purchase_credit_page_shows_day_based_pricing_message(): void
    {
        $user = $this->createProvider();
        $this->createActivePackage();

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertSeeText('One credit for every day your profile is online.');
        $response->assertSeeText('basic, pro and premium packages');
        $response->assertSeeText('2 x daily Available NOW (2 x 2 hours)');
    }

    // ---------------------------------------------------------------
    // Checkout validation
    // ---------------------------------------------------------------

    public function test_checkout_rejects_inactive_package(): void
    {
        $user = $this->createProvider();
        $inactivePackage = $this->createActivePackage(['status' => 'inactive']);

        $response = $this->actingAsProvider($user)->post('/purchase-credit/checkout', [
            'package_id' => $inactivePackage->id,
            'invoice_name' => 'Test User',
        ]);

        $response->assertSessionHasErrors('package_id');
    }

    public function test_checkout_rejects_non_existent_package(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAsProvider($user)->post('/purchase-credit/checkout', [
            'package_id' => 9999,
            'invoice_name' => 'Test User',
        ]);

        $response->assertSessionHasErrors('package_id');
    }

    public function test_checkout_requires_invoice_name(): void
    {
        $user = $this->createProvider();
        $package = $this->createActivePackage();

        $response = $this->actingAsProvider($user)->post('/purchase-credit/checkout', [
            'package_id' => $package->id,
            'invoice_name' => '',
        ]);

        $response->assertSessionHasErrors('invoice_name');
    }

    public function test_checkout_rejects_invoice_name_exceeding_max_length(): void
    {
        $user = $this->createProvider();
        $package = $this->createActivePackage();

        $response = $this->actingAsProvider($user)->post('/purchase-credit/checkout', [
            'package_id' => $package->id,
            'invoice_name' => str_repeat('a', 121),
        ]);

        $response->assertSessionHasErrors('invoice_name');
    }

    public function test_checkout_without_stripe_redirects_to_purchase_history_with_flash(): void
    {
        $user = $this->createProvider();
        $package = $this->createActivePackage(['credits' => 50, 'price' => '19.99']);

        // No SiteSetting row => Stripe is disabled => checkout completes without Stripe redirect
        $response = $this->actingAsProvider($user)->post('/purchase-credit/checkout', [
            'package_id' => $package->id,
            'invoice_name' => 'My Invoice',
        ]);

        $response->assertRedirect('/purchase-history');
        $response->assertSessionHas('checkout_success');
    }

    public function test_checkout_uses_db_price_not_frontend_input(): void
    {
        // Even if a user submits a different package via tampered request, the controller
        // reads credits/price from the DB, not from the request body.
        $user = $this->createProvider();
        $package = $this->createActivePackage(['credits' => 30, 'price' => '9.99']);

        SiteSetting::query()->create(['stripe_enabled' => false]);

        $this->actingAsProvider($user)->post('/purchase-credit/checkout', [
            'package_id' => $package->id,
            'invoice_name' => 'User',
        ]);

        // No PurchaseTransaction should be created when Stripe is disabled
        $this->assertDatabaseCount('purchase_transactions', 0);
    }

    public function test_membership_link_locks_selected_package_on_purchase_credit_page(): void
    {
        $user = $this->createProvider();
        $package = $this->createActivePackage(['credits' => 60, 'price' => '24.99']);

        $response = $this->actingAsProvider($user)
            ->get('/purchase-credit?package_id='.$package->id.'&lock_package=1');

        $response->assertOk();
        $response->assertSee('This membership selection is locked for the current checkout.');
        $response->assertSessionHas('purchase_credit_locked_package_id', $package->id);
    }

    public function test_locked_membership_package_cannot_be_changed_during_checkout(): void
    {
        $user = $this->createProvider();
        $lockedPackage = $this->createActivePackage([
            'name' => 'Locked Pack',
            'credits' => 60,
            'price' => '24.99',
            'sort_order' => 1,
        ]);
        $otherPackage = $this->createActivePackage([
            'name' => 'Other Pack',
            'credits' => 10,
            'price' => '4.99',
            'sort_order' => 2,
        ]);

        $this->actingAsProvider($user)
            ->get('/purchase-credit?package_id='.$lockedPackage->id.'&lock_package=1');

        $response = $this->actingAsProvider($user)->post('/purchase-credit/checkout', [
            'package_id' => $otherPackage->id,
            'invoice_name' => 'Locked Invoice',
        ]);

        $response->assertRedirect('/purchase-history');
        $response->assertSessionHas(
            'checkout_success',
            "Checkout started for {$lockedPackage->credits} credits (AUD $".
            number_format((float) $lockedPackage->price, 2).
            ") for {$user->name} under invoice name 'Locked Invoice'."
        );
    }

    public function test_plain_purchase_credit_page_clears_membership_package_lock(): void
    {
        $user = $this->createProvider();
        $lockedPackage = $this->createActivePackage([
            'name' => 'Locked Pack',
            'credits' => 60,
            'price' => '24.99',
            'sort_order' => 1,
        ]);
        $otherPackage = $this->createActivePackage([
            'name' => 'Other Pack',
            'credits' => 10,
            'price' => '4.99',
            'sort_order' => 2,
        ]);

        $this->actingAsProvider($user)
            ->get('/purchase-credit?package_id='.$lockedPackage->id.'&lock_package=1');

        $this->actingAsProvider($user)->get('/purchase-credit')
            ->assertSessionMissing('purchase_credit_locked_package_id');

        $response = $this->actingAsProvider($user)->post('/purchase-credit/checkout', [
            'package_id' => $otherPackage->id,
            'invoice_name' => 'Unlocked Invoice',
        ]);

        $response->assertRedirect('/purchase-history');
        $response->assertSessionHas(
            'checkout_success',
            "Checkout started for {$otherPackage->credits} credits (AUD $".
            number_format((float) $otherPackage->price, 2).
            ") for {$user->name} under invoice name 'Unlocked Invoice'."
        );
    }

    // ---------------------------------------------------------------
    // Checkout success — transaction already paid by webhook
    // ---------------------------------------------------------------

    public function test_checkout_success_redirects_to_history_when_transaction_is_paid(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;
        $profile->update(['credits' => 0]);

        PurchaseTransaction::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'stripe_session_id' => 'cs_test_already_paid',
            'credits' => 30,
            'amount' => '9.99',
            'currency' => 'AUD',
            'status' => 'paid',
            'invoice_name' => 'Test',
            'paid_at' => now(),
        ]);

        $response = $this->actingAsProvider($user)
            ->get('/purchase-credit/success?session_id=cs_test_already_paid');

        $response->assertRedirect('/purchase-history');
        $response->assertSessionHas('checkout_success');
    }

    public function test_checkout_success_does_not_re_add_credits_for_already_paid_transaction(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;
        $profile->update(['credits' => 30]);

        PurchaseTransaction::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'stripe_session_id' => 'cs_test_no_double',
            'credits' => 30,
            'amount' => '9.99',
            'currency' => 'AUD',
            'status' => 'paid',
            'invoice_name' => 'Test',
            'paid_at' => now(),
        ]);

        $this->actingAsProvider($user)
            ->get('/purchase-credit/success?session_id=cs_test_no_double');

        $this->assertDatabaseHas('provider_profiles', ['id' => $profile->id, 'credits' => 30]);
    }

    public function test_checkout_success_returns_error_for_missing_session_id(): void
    {
        $user = $this->createProvider();

        $response = $this->actingAsProvider($user)->get('/purchase-credit/success');

        $response->assertRedirect('/purchase-credit');
        $response->assertSessionHasErrors();
    }

    public function test_checkout_success_returns_error_for_transaction_owned_by_another_user(): void
    {
        $user = $this->createProvider();
        $otherUser = $this->createProvider();

        PurchaseTransaction::create([
            'user_id' => $otherUser->id,
            'provider_profile_id' => $otherUser->providerProfile->id,
            'stripe_session_id' => 'cs_test_other_user',
            'credits' => 30,
            'amount' => '9.99',
            'currency' => 'AUD',
            'status' => 'paid',
            'invoice_name' => 'Other',
            'paid_at' => now(),
        ]);

        $response = $this->actingAsProvider($user)
            ->get('/purchase-credit/success?session_id=cs_test_other_user');

        $response->assertRedirect('/purchase-credit');
        $response->assertSessionHasErrors();
    }

    // ---------------------------------------------------------------
    // Stripe webhook
    // ---------------------------------------------------------------

    public function test_webhook_route_is_reachable_without_authentication(): void
    {
        // Must return 400 (Stripe not configured) not 302 (redirect to login)
        $response = $this->post('/stripe/webhook', [], ['Stripe-Signature' => 'invalid']);

        $this->assertNotEquals(302, $response->getStatusCode());
        $this->assertNotEquals(401, $response->getStatusCode());
        $this->assertNotEquals(403, $response->getStatusCode());
    }

    public function test_webhook_returns_400_when_stripe_not_configured(): void
    {
        $response = $this->post('/stripe/webhook', [], ['Stripe-Signature' => 'invalid']);

        $response->assertStatus(400);
    }

    public function test_webhook_adds_credits_and_marks_transaction_paid(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;
        $profile->update(['credits' => 0]);

        $transaction = PurchaseTransaction::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'stripe_session_id' => 'cs_test_webhook',
            'credits' => 50,
            'amount' => '19.99',
            'currency' => 'AUD',
            'status' => 'pending',
            'invoice_name' => 'Webhook Test',
        ]);

        $this->invokeWebhookHandler($transaction, 'cs_test_webhook');

        $this->assertDatabaseHas('purchase_transactions', [
            'id' => $transaction->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('provider_profiles', [
            'id' => $profile->id,
            'credits' => 50,
        ]);
        $this->assertDatabaseHas('credit_logs', [
            'user_id' => $user->id,
            'amount' => 50,
            'type' => 'purchase_credit',
            'related_payment_id' => $transaction->id,
        ]);
        $this->assertDatabaseHas('invoices', [
            'payment_id' => $transaction->id,
            'user_id' => $user->id,
            'credits' => 50,
            'payment_provider' => 'stripe',
        ]);
    }

    public function test_webhook_applies_bonus_credits_and_generates_invoice(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;
        $profile->update(['credits' => 0]);

        $transaction = PurchaseTransaction::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'provider' => 'stripe',
            'credits' => 30,
            'bonus_credits' => 5,
            'amount' => '19.99',
            'currency' => 'AUD',
            'status' => 'pending',
            'invoice_name' => 'Bonus Test',
        ]);

        $this->invokeWebhookHandler($transaction, 'cs_test_bonus');

        $this->assertDatabaseHas('provider_profiles', [
            'id' => $profile->id,
            'credits' => 35,
        ]);
        $this->assertDatabaseHas('invoices', [
            'payment_id' => $transaction->id,
            'credits' => 35,
        ]);
    }

    public function test_webhook_credits_only_target_profile_under_same_account(): void
    {
        $user = $this->createProvider();
        $primaryProfile = $user->providerProfile;
        $secondaryProfile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Second Profile',
            'slug' => 'second-profile-'.$user->id,
            'credits' => 0,
        ]);
        $primaryProfile->update(['credits' => 0]);

        $transaction = PurchaseTransaction::create([
            'user_id' => $user->id,
            'provider_profile_id' => $primaryProfile->id,
            'stripe_session_id' => 'cs_test_profile_scope',
            'credits' => 30,
            'amount' => '9.99',
            'currency' => 'AUD',
            'status' => 'pending',
            'invoice_name' => 'Scoped Profile',
        ]);

        $this->invokeWebhookHandler($transaction, 'cs_test_profile_scope');

        $this->assertDatabaseHas('provider_profiles', ['id' => $primaryProfile->id, 'credits' => 30]);
        $this->assertDatabaseHas('provider_profiles', ['id' => $secondaryProfile->id, 'credits' => 0]);
    }

    public function test_webhook_sends_purchase_email_on_successful_payment(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;
        $profile->update(['credits' => 0]);

        $transaction = PurchaseTransaction::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'stripe_session_id' => 'cs_test_email',
            'credits' => 30,
            'amount' => '9.99',
            'currency' => 'AUD',
            'status' => 'pending',
            'invoice_name' => 'Email Test',
        ]);

        $emailAction = $this->createMock(SendCreditPurchaseEmail::class);
        $emailAction->expects($this->once())
            ->method('execute')
            ->with($this->callback(fn ($t) => $t->id === $transaction->id));

        $this->app->instance(SendCreditPurchaseEmail::class, $emailAction);
        $this->app->instance(ProcessReferralRewardForFirstPayment::class, $this->createMock(ProcessReferralRewardForFirstPayment::class));

        $controller = $this->app->make(HandleStripeWebhook::class);
        $method = new \ReflectionMethod($controller, 'handleCheckoutSessionCompleted');
        $method->setAccessible(true);

        $session = (object) [
            'payment_status' => 'paid',
            'payment_intent' => 'pi_test_email',
            'metadata' => (object) [
                'transaction_id' => (string) $transaction->id,
                'user_id' => (string) $transaction->user_id,
                'provider_profile_id' => (string) $transaction->provider_profile_id,
                'credits' => (string) $transaction->credits,
            ],
            'id' => 'cs_test_email',
        ];

        $method->invoke($controller, $session);
    }

    public function test_webhook_is_idempotent_on_duplicate_delivery(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;
        $profile->update(['credits' => 0]);

        $transaction = PurchaseTransaction::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'stripe_session_id' => 'cs_test_idempotent',
            'credits' => 20,
            'amount' => '7.99',
            'currency' => 'AUD',
            'status' => 'pending',
            'invoice_name' => 'Idempotent Test',
        ]);

        $this->invokeWebhookHandler($transaction, 'cs_test_idempotent');
        $this->invokeWebhookHandler($transaction, 'cs_test_idempotent');

        $profile->refresh();
        $this->assertEquals(20, $profile->credits);
    }

    public function test_webhook_does_not_add_credits_when_payment_not_paid(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;
        $profile->update(['credits' => 0]);

        $transaction = PurchaseTransaction::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'stripe_session_id' => 'cs_test_unpaid',
            'credits' => 30,
            'amount' => '9.99',
            'currency' => 'AUD',
            'status' => 'pending',
            'invoice_name' => 'Unpaid Test',
        ]);

        $this->invokeWebhookHandler($transaction, 'cs_test_unpaid', 'unpaid');

        $this->assertDatabaseHas('purchase_transactions', [
            'id' => $transaction->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseHas('provider_profiles', ['id' => $profile->id, 'credits' => 0]);
    }

    public function test_webhook_logs_warning_when_no_transaction_id_in_metadata(): void
    {
        Log::spy();

        $this->app->instance(ProcessReferralRewardForFirstPayment::class, $this->createMock(ProcessReferralRewardForFirstPayment::class));
        $controller = $this->app->make(HandleStripeWebhook::class);
        $method = new \ReflectionMethod($controller, 'handleCheckoutSessionCompleted');
        $method->setAccessible(true);

        $session = (object) [
            'payment_status' => 'paid',
            'payment_intent' => 'pi_test',
            'metadata' => (object) [],
            'id' => 'cs_no_tx',
        ];

        $method->invoke($controller, $session);

        Log::shouldHaveReceived('warning')->once();
    }

    // ---------------------------------------------------------------
    // Purchase History page
    // ---------------------------------------------------------------

    public function test_purchase_history_page_lists_own_transactions(): void
    {
        $user = $this->createProvider();

        PurchaseTransaction::create([
            'user_id' => $user->id,
            'provider_profile_id' => $user->providerProfile->id,
            'credits' => 30,
            'amount' => '9.99',
            'currency' => 'AUD',
            'status' => 'paid',
            'invoice_name' => 'My Invoice',
            'paid_at' => now(),
        ]);

        $response = $this->actingAsProvider($user)->get('/purchase-history');

        $response->assertOk();
        $response->assertViewIs('subscription.purchase-history');
        $response->assertViewHas('purchases', fn ($p) => $p->total() === 1);
    }

    public function test_purchase_history_does_not_show_other_users_transactions(): void
    {
        $user = $this->createProvider();
        $otherUser = $this->createProvider();

        PurchaseTransaction::create([
            'user_id' => $otherUser->id,
            'provider_profile_id' => $otherUser->providerProfile->id,
            'credits' => 30,
            'amount' => '9.99',
            'currency' => 'AUD',
            'status' => 'paid',
            'invoice_name' => 'Other',
            'paid_at' => now(),
        ]);

        $response = $this->actingAsProvider($user)->get('/purchase-history');

        $response->assertOk();
        $response->assertViewHas('purchases', fn ($p) => $p->total() === 0);
    }

    // ---------------------------------------------------------------
    // Payment Subscription page
    // ---------------------------------------------------------------

    public function test_payment_subscription_page_renders(): void
    {
        $user = $this->createProvider();

        CreditPackage::create([
            'name' => 'Starter Pack',
            'credits' => 30,
            'price' => '9.99',
            'status' => 'active',
            'sort_order' => 1,
        ]);

        $response = $this->actingAsProvider($user)->get('/payment-subscription');

        $response->assertOk();
        $response->assertViewIs('subscription.payment-subscription');
        $response->assertViewHas('packages', fn ($p) => $p->count() === 1);
        $response->assertSeeText('Simple and fair credits pricing for all profiles.');
        $response->assertSeeText('Buy credits');
    }

    // ---------------------------------------------------------------
    // Membership page (public)
    // ---------------------------------------------------------------

    public function test_membership_page_is_publicly_accessible(): void
    {
        $response = $this->get('/membership');

        $response->assertOk();
        $response->assertViewIs('subscription.membership');
    }

    // ---------------------------------------------------------------
    // Helper: directly invoke webhook handler (bypasses signature check)
    // ---------------------------------------------------------------

    private function invokeWebhookHandler(
        PurchaseTransaction $transaction,
        string $sessionId,
        string $paymentStatus = 'paid'
    ): void {
        Mail::fake();

        $this->app->instance(ProcessReferralRewardForFirstPayment::class, $this->createMock(ProcessReferralRewardForFirstPayment::class));
        $controller = $this->app->make(HandleStripeWebhook::class);
        $method = new \ReflectionMethod($controller, 'handleCheckoutSessionCompleted');
        $method->setAccessible(true);

        $session = (object) [
            'payment_status' => $paymentStatus,
            'payment_intent' => 'pi_test_'.uniqid(),
            'metadata' => (object) [
                'transaction_id' => (string) $transaction->id,
                'user_id' => (string) $transaction->user_id,
                'provider_profile_id' => (string) $transaction->provider_profile_id,
                'credits' => (string) $transaction->credits,
            ],
            'id' => $sessionId,
        ];

        $method->invoke($controller, $session);
    }
}
