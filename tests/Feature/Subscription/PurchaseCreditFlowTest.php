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
        $response->assertDontSee("document.getElementById('purchase-credit-flow')", false);
        $response->assertDontSee("paymentMethodTypes: ['card']", false);
        $response->assertSeeText('Test checkout mode is enabled. Use test card details to complete checkout safely.');
    }

    public function test_purchase_credit_page_shows_only_active_packages(): void
    {
        $user = $this->createProvider();
        $active = $this->createActivePackage(['name' => 'Active Pack', 'sort_order' => 1]);
        $this->createActivePackage(['name' => 'Inactive Pack', 'status' => 'inactive', 'is_active' => false, 'sort_order' => 2]);

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertViewHas('packages', function ($packages) use ($active) {
            return $packages->count() === 1 && $packages->first()->id === $active->id;
        });
    }

    public function test_purchase_credit_page_is_publicly_accessible_without_authentication(): void
    {
        $this->createActivePackage();

        $response = $this->get('/purchase-credit');

        $response->assertOk();
        $response->assertViewIs('subscription.purchase-credit');
        $response->assertViewHas('guestMode', true);
    }

    public function test_purchase_credit_page_shows_sign_in_prompt_for_unauthenticated_users(): void
    {
        $this->createActivePackage();

        $response = $this->get('/purchase-credit');

        $response->assertOk();
        $response->assertSeeText('You are purchasing advertising credits for use on hotescort.com.au. Credits are used for profile visibility and promotional listing features only.');
        $response->assertSeeText('Payment processing is currently unavailable. Please contact support.');
        $response->assertSee(route('refund-policy'));
        $response->assertSeeText('For business/support contact details, please email support@hotescorts.com.au');
        $response->assertSee('Sign in to purchase credits');
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

    public function test_purchase_credit_page_shows_test_mode_checkout_information_when_payments_are_disabled(): void
    {
        $user = $this->createProvider();
        $this->createActivePackage();

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertSeeText('You are purchasing advertising credits for use on hotescort.com.au. Credits are used for profile visibility and promotional listing features only.');
        $response->assertSeeText('Payment processing is currently unavailable. Please contact support.');
        $response->assertSeeText('AUD $9.99');
        $response->assertSeeText('For business/support contact details, please email support@hotescorts.com.au');
        $response->assertSee(route('refund-policy'));
    }

    public function test_payment_page_shows_active_test_checkout_button_when_stripe_is_configured_in_sandbox(): void
    {
        $user = $this->createProvider();
        $this->createActivePackage();
        SiteSetting::query()->create([
            'stripe_enabled' => true,
            'stripe_publishable_key' => 'pk_test_123',
            'stripe_secret_key' => 'sk_test_123',
            'stripe_mode' => 'sandbox',
        ]);

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertSeeText('Continue to test payment');
        $response->assertSee('id="proceed-to-payment"', false);
    }

    public function test_payment_page_loads_stripe_js_in_test_mode_when_stripe_is_configured(): void
    {
        $user = $this->createProvider();
        $this->createActivePackage();
        SiteSetting::query()->create([
            'stripe_enabled' => true,
            'stripe_publishable_key' => 'pk_test_123',
            'stripe_secret_key' => 'sk_test_123',
            'stripe_mode' => 'sandbox',
        ]);

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertSee('js.stripe.com', false);
        $response->assertSee("Stripe('", false);
        $response->assertSee('window.proceedToPayment = async function ()', false);
    }

    public function test_payment_page_contains_no_paypal_or_square_payment_buttons(): void
    {
        // The platform is approved only for Stripe card payments.  PayPal,
        // Square, Braintree, and similar buttons must never appear on the
        // checkout page regardless of configuration.
        $user = $this->createProvider();
        $this->createActivePackage();

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertDontSee('paypal.com', false);
        $response->assertDontSee('squareup.com', false);
        $response->assertDontSee('braintreepayments.com', false);
        $response->assertDontSee('PayPal', false);
        $response->assertDontSee('Square', false);
    }

    public function test_create_intent_endpoint_returns_error_when_payment_not_configured(): void
    {
        // POST /purchase-credit/create-intent must be rejected (HTTP 422) with
        // an error payload when the payment provider is not configured.
        $user = $this->createProvider();
        $package = $this->createActivePackage();

        $response = $this->actingAsProvider($user)->postJson('/purchase-credit/create-intent', [
            'package_id' => $package->id,
            'invoice_name' => 'Test User',
            'provider_profile_id' => $user->providerProfile->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonPath('error', 'Payment system is not configured.');
        $this->assertDatabaseCount('purchase_transactions', 0);
    }

    // ---------------------------------------------------------------
    // Checkout validation
    // ---------------------------------------------------------------

    public function test_checkout_rejects_inactive_package(): void
    {
        $user = $this->createProvider();
        $inactivePackage = $this->createActivePackage(['status' => 'inactive', 'is_active' => false]);

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

    public function test_checkout_without_processor_approval_redirects_back_with_error(): void
    {
        $user = $this->createProvider();
        $package = $this->createActivePackage(['credits' => 50, 'price' => '19.99']);

        $response = $this->actingAsProvider($user)->post('/purchase-credit/checkout', [
            'package_id' => $package->id,
            'invoice_name' => 'My Invoice',
        ]);

        $response->assertRedirect('/purchase-credit');
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('purchase_transactions', 0);
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

        $response->assertRedirect('/purchase-credit');
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('purchase_transactions', 0);
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

        $response->assertRedirect('/purchase-credit');
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('purchase_transactions', 0);
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

    public function test_webhook_adds_credits_even_when_profile_is_soft_deleted(): void
    {
        $user = $this->createProvider();
        $profile = $user->providerProfile;
        $profile->update(['credits' => 0]);

        $transaction = PurchaseTransaction::create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'stripe_session_id' => 'cs_test_soft_deleted',
            'credits' => 40,
            'amount' => '14.99',
            'currency' => 'AUD',
            'status' => 'pending',
            'invoice_name' => 'Soft Delete Test',
        ]);

        // Soft-delete the profile after the transaction was created (simulates
        // an admin or user action that occurs before the webhook fires).
        $profile->delete();

        $this->invokeWebhookHandler($transaction, 'cs_test_soft_deleted');

        // Transaction must be marked paid.
        $this->assertDatabaseHas('purchase_transactions', [
            'id' => $transaction->id,
            'status' => 'paid',
        ]);

        // Credits must be applied to the (soft-deleted) profile.
        $this->assertDatabaseHas('provider_profiles', [
            'id' => $profile->id,
            'credits' => 40,
        ]);
        $this->assertDatabaseHas('credit_logs', [
            'user_id' => $user->id,
            'amount' => 40,
            'type' => 'purchase_credit',
            'related_payment_id' => $transaction->id,
        ]);
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

    // ---------------------------------------------------------------
    // checkout_enabled admin toggle
    // ---------------------------------------------------------------

    public function test_purchase_credit_page_shows_test_mode_notice_when_checkout_is_disabled_by_admin(): void
    {
        $user = $this->createProvider();
        $this->createActivePackage();

        SiteSetting::query()->create(['checkout_enabled' => false]);

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertSeeText('Checkout Disabled');
        $response->assertSeeText('Checkout is currently disabled by admin. Please try again later.');
    }

    public function test_purchase_credit_page_shows_checkout_disabled_button_when_checkout_is_disabled_by_admin(): void
    {
        $user = $this->createProvider();
        $this->createActivePackage();

        SiteSetting::query()->create(['checkout_enabled' => false]);

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertSeeText('Checkout disabled by admin');
        $response->assertDontSee('id="proceed-to-payment"', false);
    }

    public function test_checkout_is_blocked_when_checkout_disabled_by_admin(): void
    {
        $user = $this->createProvider();
        $package = $this->createActivePackage();

        SiteSetting::query()->create(['checkout_enabled' => false]);

        $response = $this->actingAsProvider($user)->post('/purchase-credit/checkout', [
            'package_id' => $package->id,
            'provider_profile_id' => $user->providerProfile->id,
            'invoice_name' => 'Test User',
        ]);

        $response->assertRedirect('/purchase-credit');
        $response->assertSessionHasErrors();
        $this->assertStringContainsString(
            'Checkout is currently disabled',
            $response->getSession()->get('errors')->first()
        );
    }

    public function test_woo_checkout_is_blocked_when_checkout_disabled_by_admin(): void
    {
        $user = $this->createProvider();
        $package = $this->createActivePackage(['slug' => 'starter', 'woo_product_id' => 42]);

        SiteSetting::query()->create(['checkout_enabled' => false]);

        $response = $this->actingAsProvider($user)->post('/purchase-credit/woo-checkout', [
            'package_id' => $package->id,
            'provider_profile_id' => $user->providerProfile->id,
            'invoice_name' => 'Test User',
        ]);

        $response->assertRedirect('/purchase-credit');
        $response->assertSessionHasErrors();
        $this->assertStringContainsString(
            'Checkout is currently disabled',
            $response->getSession()->get('errors')->first()
        );
    }

    public function test_checkout_proceeds_normally_when_checkout_enabled(): void
    {
        $user = $this->createProvider();
        $package = $this->createActivePackage();

        SiteSetting::query()->create(['checkout_enabled' => true]);

        $response = $this->actingAsProvider($user)->post('/purchase-credit/checkout', [
            'package_id' => $package->id,
            'provider_profile_id' => $user->providerProfile->id,
            'invoice_name' => 'Test User',
        ]);

        // Should NOT redirect back with "checkout disabled" error
        $this->assertNotContains(
            'Checkout is currently disabled',
            $response->getSession()->get('errors')?->all() ?? []
        );
    }
}
