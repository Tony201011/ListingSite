<?php

namespace Tests\Feature;

use Database\Seeders\AntiSpamPolicySeeder;
use Database\Seeders\HowCreditsWorkPageSeeder;
use Database\Seeders\PricingPageSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreditSystemClarityTest extends TestCase
{
    use RefreshDatabase;

    public function test_credit_pages_explain_prepaid_credits_scope_and_platform_payment_boundary(): void
    {
        $this->seed([
            PricingPageSeeder::class,
            HowCreditsWorkPageSeeder::class,
            AntiSpamPolicySeeder::class,
        ]);

        $pricingResponse = $this->get('/pricing');
        $pricingResponse->assertOk();
        $pricingResponse->assertSeeText('Advertisers purchase prepaid advertising credits.');
        $pricingResponse->assertSeeText('Credits are used for profile visibility and promotional listing features');
        $pricingResponse->assertSeeText('All payments on this platform are exclusively for purchasing advertising credits and promotional listing packages. No payments are processed between visitors and advertisers.');

        $howCreditsWorkResponse = $this->get('/how-credits-work');
        $howCreditsWorkResponse->assertOk();
        $howCreditsWorkResponse->assertSeeText('1 credit keeps one approved profile visible for one day.');
        $howCreditsWorkResponse->assertSeeText('Credits are not deducted while a profile is hidden, suspended, or under review.');
        $howCreditsWorkResponse->assertSeeText('If the credit balance reaches zero, the profile is paused automatically.');
        $howCreditsWorkResponse->assertSeeText('Used credits are not refundable.');

        $creditPolicyResponse = $this->get('/credit-usage-and-expiry-policy');
        $creditPolicyResponse->assertOk();
        $creditPolicyResponse->assertSeeText('Unused credits may be handled according to the refund policy.');
    }
}
