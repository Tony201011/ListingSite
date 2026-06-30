<?php

namespace Tests\Feature\Subscription;

use App\Models\CreditPackage;
use App\Models\CreditPurchase;
use App\Models\ProviderProfile;
use App\Models\PurchaseTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseCreditSuccessRouteTest extends TestCase
{
    use RefreshDatabase;

    public function test_success_route_works_without_active_profile_session(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => $user->name,
            'slug' => 'provider-'.$user->id,
        ]);

        PurchaseTransaction::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'stripe_session_id' => 'cs_test_missing_profile_session',
            'credits' => 30,
            'amount' => '9.99',
            'currency' => 'AUD',
            'status' => 'paid',
            'invoice_name' => 'Paid',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/purchase-credit/success?session_id=cs_test_missing_profile_session');

        $response->assertRedirect('/purchase-history');
        $response->assertSessionHas('checkout_success');
    }

    public function test_woocommerce_success_route_redirects_paid_purchase_back_to_history(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Provider Profile',
            'slug' => 'provider-profile-'.$user->id,
        ]);
        $package = CreditPackage::query()->create([
            'name' => 'Starter',
            'slug' => 'starter',
            'credits' => 30,
            'price' => '9.99',
            'currency' => 'AUD',
            'status' => 'active',
            'woo_product_id' => 42,
        ]);
        $purchase = CreditPurchase::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'credit_package_id' => $package->id,
            'credits' => 30,
            'amount_cents' => 999,
            'currency' => 'AUD',
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/purchase-credit/success?provider=woocommerce&purchase_uuid='.$purchase->uuid);

        $response->assertRedirect('/purchase-history');
        $response->assertSessionHas('checkout_success');
    }

    public function test_woocommerce_success_route_shows_processing_message_for_pending_purchase(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Provider Profile',
            'slug' => 'provider-profile-'.$user->id,
        ]);
        $package = CreditPackage::query()->create([
            'name' => 'Starter',
            'slug' => 'starter',
            'credits' => 30,
            'price' => '9.99',
            'currency' => 'AUD',
            'status' => 'active',
            'woo_product_id' => 42,
        ]);
        $purchase = CreditPurchase::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'credit_package_id' => $package->id,
            'credits' => 30,
            'amount_cents' => 999,
            'currency' => 'AUD',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/purchase-credit/success?provider=woocommerce&purchase_uuid='.$purchase->uuid);

        $response->assertRedirect('/purchase-credit');
        $response->assertSessionHas('checkout_success', 'Your WooCommerce payment is being processed. Credits will appear shortly.');
    }
}
