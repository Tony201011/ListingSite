<?php

namespace Tests\Feature\Subscription;

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
}
