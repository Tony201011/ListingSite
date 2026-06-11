<?php

namespace Tests\Feature;

use App\Models\PrivacyPolicy;
use App\Models\ProviderProfile;
use App\Models\RefundPolicy;
use App\Models\TermCondition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleListingAndPolicyPagesTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Sample listing
    // ---------------------------------------------------------------

    public function test_sample_listing_page_redirects_to_first_approved_profile(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Sample Escort',
            'slug' => 'sample-escort',
            'profile_sequence' => 1,
            'profile_status' => 'approved',
            'age' => 25,
        ]);

        $response = $this->get(route('sample-listing'));

        $response->assertRedirect($profile->getEscortUrl());
    }

    public function test_sample_listing_page_redirects_to_search_when_no_approved_profiles_exist(): void
    {
        $response = $this->get(route('sample-listing'));

        $response->assertRedirect(route('escorts.search'));
    }

    // ---------------------------------------------------------------
    // Terms and conditions
    // ---------------------------------------------------------------

    public function test_terms_and_conditions_page_loads(): void
    {
        TermCondition::query()->create([
            'title' => 'Terms & Conditions',
            'content' => '<p>Terms content here.</p>',
            'is_active' => true,
        ]);

        $response = $this->get(route('terms-and-conditions'));

        $response->assertOk();
        $response->assertSeeText('Terms and Conditions');
    }

    public function test_terms_and_conditions_page_loads_without_content(): void
    {
        $response = $this->get(route('terms-and-conditions'));

        $response->assertOk();
        $response->assertSeeText('Terms and Conditions');
    }

    // ---------------------------------------------------------------
    // Privacy policy
    // ---------------------------------------------------------------

    public function test_privacy_policy_page_loads(): void
    {
        PrivacyPolicy::query()->create([
            'title' => 'Privacy Policy',
            'content' => '<p>Privacy content here.</p>',
            'is_active' => true,
        ]);

        $response = $this->get(route('privacy-policy'));

        $response->assertOk();
        $response->assertSeeText('Privacy Policy');
    }

    public function test_privacy_policy_page_loads_without_content(): void
    {
        $response = $this->get(route('privacy-policy'));

        $response->assertOk();
        $response->assertSeeText('Privacy Policy');
    }

    // ---------------------------------------------------------------
    // Refund policy
    // ---------------------------------------------------------------

    public function test_refund_policy_page_loads(): void
    {
        RefundPolicy::query()->create([
            'title' => 'Refund Policy',
            'content' => '<p>Refund content here.</p>',
            'is_active' => true,
        ]);

        $response = $this->get(route('refund-policy'));

        $response->assertOk();
        $response->assertSeeText('Refund Policy');
    }

    public function test_refund_policy_page_loads_without_content(): void
    {
        $response = $this->get(route('refund-policy'));

        $response->assertOk();
        $response->assertSeeText('Refund Policy');
    }
}
