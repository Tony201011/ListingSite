<?php

namespace Tests\Feature;

use App\Actions\SendBookingEnquiryEmail;
use App\Http\Middleware\CheckProfileSteps;
use App\Http\Middleware\EnsureProfileSelected;
use App\Models\CreditPackage;
use App\Models\ProviderProfile;
use App\Models\PurchaseTransaction;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

/**
 * Confirms that no real card payments can be processed until the payment
 * processor has approved the account (i.e. stripe_mode is set to 'live').
 *
 * Requirements covered:
 *  - Real card payments are disabled before processor approval.
 *  - Payment page can be in test mode.
 *  - Stripe, PayPal, Square and other unsupported payment buttons are not shown
 *    unless the provider is in live mode.
 *  - Payments are not processed through an unapproved provider.
 *  - Bookings, deposits, appointment payments, and payments between visitors
 *    and advertisers are not processed by the platform.
 */
class NoRealPaymentsBeforeApprovalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([CheckProfileSteps::class, EnsureProfileSelected::class]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
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

    private function stripeTestModeSettings(): void
    {
        SiteSetting::query()->create([
            'stripe_enabled' => true,
            'stripe_mode' => 'test',
            'stripe_publishable_key' => 'pk_test_placeholder',
            'stripe_secret_key' => 'sk_test_placeholder',
        ]);
    }

    private function stripeLiveModeSettings(): void
    {
        SiteSetting::query()->create([
            'stripe_enabled' => true,
            'stripe_mode' => 'live',
            'stripe_publishable_key' => 'pk_live_placeholder',
            'stripe_secret_key' => 'sk_live_placeholder',
        ]);
    }

    // ---------------------------------------------------------------
    // Real card payments are disabled before processor approval
    // ---------------------------------------------------------------

    public function test_checkout_is_blocked_when_stripe_is_in_test_mode(): void
    {
        $user = $this->createProvider();
        $package = $this->createActivePackage();
        $this->stripeTestModeSettings();

        $response = $this->actingAsProvider($user)->post('/purchase-credit/checkout', [
            'package_id' => $package->id,
            'invoice_name' => 'Test Invoice',
        ]);

        $response->assertRedirect('/purchase-credit');
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('purchase_transactions', 0);
    }

    public function test_checkout_is_blocked_when_stripe_enabled_but_no_mode_set(): void
    {
        $user = $this->createProvider();
        $package = $this->createActivePackage();

        SiteSetting::query()->create([
            'stripe_enabled' => true,
            'stripe_publishable_key' => 'pk_test_placeholder',
            'stripe_secret_key' => 'sk_test_placeholder',
            // stripe_mode intentionally omitted — defaults to null, not 'live'
        ]);

        $response = $this->actingAsProvider($user)->post('/purchase-credit/checkout', [
            'package_id' => $package->id,
            'invoice_name' => 'Test Invoice',
        ]);

        $response->assertRedirect('/purchase-credit');
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('purchase_transactions', 0);
    }

    public function test_checkout_is_blocked_when_no_site_settings_exist(): void
    {
        $user = $this->createProvider();
        $package = $this->createActivePackage();

        $response = $this->actingAsProvider($user)->post('/purchase-credit/checkout', [
            'package_id' => $package->id,
            'invoice_name' => 'Test Invoice',
        ]);

        $response->assertRedirect('/purchase-credit');
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('purchase_transactions', 0);
    }

    public function test_checkout_is_blocked_when_stripe_is_disabled_even_with_live_mode(): void
    {
        $user = $this->createProvider();
        $package = $this->createActivePackage();

        SiteSetting::query()->create([
            'stripe_enabled' => false,
            'stripe_mode' => 'live',
            'stripe_publishable_key' => 'pk_live_placeholder',
            'stripe_secret_key' => 'sk_live_placeholder',
        ]);

        $response = $this->actingAsProvider($user)->post('/purchase-credit/checkout', [
            'package_id' => $package->id,
            'invoice_name' => 'Test Invoice',
        ]);

        $response->assertRedirect('/purchase-credit');
        $response->assertSessionHasErrors();
        $this->assertDatabaseCount('purchase_transactions', 0);
    }

    // ---------------------------------------------------------------
    // Payment intent endpoint blocked before processor approval
    // ---------------------------------------------------------------

    public function test_create_payment_intent_returns_error_when_stripe_in_test_mode(): void
    {
        $user = $this->createProvider();
        $package = $this->createActivePackage();
        $this->stripeTestModeSettings();

        $response = $this->actingAsProvider($user)->postJson('/purchase-credit/create-intent', [
            'package_id' => $package->id,
            'invoice_name' => 'Test Invoice',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['error' => 'Payment system is not configured.']);
        $this->assertDatabaseCount('purchase_transactions', 0);
    }

    public function test_create_payment_intent_returns_error_when_no_settings_exist(): void
    {
        $user = $this->createProvider();
        $package = $this->createActivePackage();

        $response = $this->actingAsProvider($user)->postJson('/purchase-credit/create-intent', [
            'package_id' => $package->id,
            'invoice_name' => 'Test Invoice',
        ]);

        $response->assertStatus(422);
        $response->assertJsonFragment(['error' => 'Payment system is not configured.']);
        $this->assertDatabaseCount('purchase_transactions', 0);
    }

    // ---------------------------------------------------------------
    // Payment page shows test mode — no live payment buttons rendered
    // ---------------------------------------------------------------

    public function test_payment_page_shows_test_mode_banner_when_stripe_in_test_mode(): void
    {
        $user = $this->createProvider();
        $this->createActivePackage();
        $this->stripeTestModeSettings();

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertViewHas('paymentEnabled', false);
        $response->assertSeeText('Payment processing is currently in test mode for processor review.');
    }

    public function test_payment_page_shows_test_mode_banner_when_no_settings_exist(): void
    {
        $user = $this->createProvider();
        $this->createActivePackage();

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertViewHas('paymentEnabled', false);
        $response->assertSeeText('Payment processing is currently in test mode for processor review.');
    }

    // ---------------------------------------------------------------
    // No Stripe JS / payment element loaded before processor approval
    // ---------------------------------------------------------------

    public function test_stripe_js_sdk_is_not_loaded_on_payment_page_in_test_mode(): void
    {
        $user = $this->createProvider();
        $this->createActivePackage();
        $this->stripeTestModeSettings();

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertDontSee('js.stripe.com', false);
        $response->assertDontSee("Stripe('", false);
        $response->assertDontSee('payment-element', false);
    }

    public function test_stripe_js_sdk_is_not_loaded_when_no_settings_exist(): void
    {
        $user = $this->createProvider();
        $this->createActivePackage();

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertDontSee('js.stripe.com', false);
        $response->assertDontSee("Stripe('", false);
    }

    // ---------------------------------------------------------------
    // No PayPal or Square buttons shown
    // ---------------------------------------------------------------

    public function test_no_paypal_button_shown_on_payment_page(): void
    {
        $user = $this->createProvider();
        $this->createActivePackage();
        $this->stripeTestModeSettings();

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertDontSee('paypal', false);
        $response->assertDontSee('PayPal', false);
        $response->assertDontSee('paypal.com', false);
    }

    public function test_no_square_button_shown_on_payment_page(): void
    {
        $user = $this->createProvider();
        $this->createActivePackage();
        $this->stripeTestModeSettings();

        $response = $this->actingAsProvider($user)->get('/purchase-credit');

        $response->assertOk();
        $response->assertDontSee('squareup.com', false);
        $response->assertDontSee('Square Payments', false);
    }

    public function test_no_paypal_or_square_buttons_shown_when_payment_not_configured(): void
    {
        // Guest / unauthenticated view
        $this->createActivePackage();

        $response = $this->get('/purchase-credit');

        $response->assertOk();
        $response->assertDontSee('paypal', false);
        $response->assertDontSee('PayPal', false);
        $response->assertDontSee('squareup.com', false);
        $response->assertDontSee('Square Payments', false);
    }

    // ---------------------------------------------------------------
    // Checkout success is blocked when not in live mode
    // ---------------------------------------------------------------

    public function test_checkout_success_redirects_with_not_configured_error_when_in_test_mode(): void
    {
        $user = $this->createProvider();
        $package = $this->createActivePackage();
        $this->stripeTestModeSettings();

        $transaction = PurchaseTransaction::create([
            'user_id' => $user->id,
            'provider_profile_id' => $user->providerProfile->id,
            'provider' => 'stripe',
            'credit_package_id' => $package->id,
            'credits' => 30,
            'bonus_credits' => 0,
            'amount' => '9.99',
            'currency' => 'AUD',
            'status' => 'pending',
            'invoice_name' => 'Test',
            'provider_checkout_id' => 'cs_test_abc123',
            'stripe_session_id' => 'cs_test_abc123',
        ]);

        $response = $this->actingAsProvider($user)->get('/purchase-credit/success?session_id=cs_test_abc123');

        $response->assertRedirect('/purchase-credit');
        $response->assertSessionHasErrors();

        // Transaction must remain in 'pending' status — no credits awarded
        $this->assertDatabaseHas('purchase_transactions', [
            'id' => $transaction->id,
            'status' => 'pending',
        ]);
    }

    // ---------------------------------------------------------------
    // Booking enquiry does NOT process financial payments
    // ---------------------------------------------------------------

    public function test_booking_enquiry_does_not_create_purchase_transaction(): void
    {
        $mock = Mockery::mock(SendBookingEnquiryEmail::class);
        $mock->shouldReceive('execute')->andReturnNull();
        $this->app->instance(SendBookingEnquiryEmail::class, $mock);

        $provider = $this->createProvider();

        $this->from('/profile/test')->post('/booking-enquiry', [
            'user_id' => $provider->id,
            'name' => 'Visitor Name',
            'email' => 'visitor@example.com',
            'phone' => '0400000000',
            'datetime' => now()->addDay()->setTime(14, 0)->toDateTimeString(),
            'services' => 'Massage',
            'duration' => '60 mins',
            'location' => 'Sydney',
            'message' => 'Looking to make a booking.',
        ]);

        // A booking enquiry is a contact/messaging record only —
        // no financial transaction should ever be created.
        $this->assertDatabaseCount('purchase_transactions', 0);
        $this->assertDatabaseCount('booking_enquiries', 1);
    }

    public function test_booking_enquiry_stores_contact_message_not_payment_data(): void
    {
        $mock = Mockery::mock(SendBookingEnquiryEmail::class);
        $mock->shouldReceive('execute')->andReturnNull();
        $this->app->instance(SendBookingEnquiryEmail::class, $mock);

        $provider = $this->createProvider();

        $this->from('/profile/test')->post('/booking-enquiry', [
            'user_id' => $provider->id,
            'name' => 'Visitor Name',
            'email' => 'visitor@example.com',
            'phone' => '0400000000',
            'datetime' => now()->addDay()->setTime(10, 0)->toDateTimeString(),
            'services' => 'Companionship',
            'duration' => '2 hours',
            'location' => 'Melbourne',
            'message' => 'Please confirm availability.',
        ]);

        // The enquiry is stored as a contact message (pending status, unread)
        $this->assertDatabaseHas('booking_enquiries', [
            'user_id' => $provider->id,
            'email' => 'visitor@example.com',
            'name' => 'Visitor Name',
            'status' => 'pending',
            'is_read' => false,
        ]);

        // No credits, amounts, or payment data should ever be stored
        $this->assertDatabaseCount('purchase_transactions', 0);
    }

    // ---------------------------------------------------------------
    // Webhook blocked before processor approval
    // ---------------------------------------------------------------

    public function test_stripe_webhook_returns_error_when_stripe_in_test_mode(): void
    {
        $this->stripeTestModeSettings();

        $response = $this->post('/stripe/webhook', [], ['Stripe-Signature' => 'invalid']);

        // Stripe is enabled but mode is not 'live' — isConfigured() returns false
        $response->assertStatus(400);
        $this->assertDatabaseCount('purchase_transactions', 0);
    }

    public function test_stripe_webhook_returns_error_when_no_settings_exist(): void
    {
        $response = $this->post('/stripe/webhook', [], ['Stripe-Signature' => 'invalid']);

        $response->assertStatus(400);
        $this->assertDatabaseCount('purchase_transactions', 0);
    }
}
