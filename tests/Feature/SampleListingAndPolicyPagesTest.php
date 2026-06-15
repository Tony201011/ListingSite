<?php

namespace Tests\Feature;

use App\Models\PrivacyPolicy;
use App\Models\Availability;
use App\Models\OnlineUser;
use App\Models\ProviderProfile;
use App\Models\RefundPolicy;
use App\Models\TermCondition;
use App\Models\AgeAndConsentPolicy;
use App\Models\AntiSpamPolicy;
use App\Models\ContentModerationPolicy;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SampleListingAndPolicyPagesTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // Sample listing
    // ---------------------------------------------------------------

    public function test_sample_listing_page_redirects_to_first_demo_safe_profile(): void
    {
        $liveUser = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        ProviderProfile::query()->create([
            'user_id' => $liveUser->id,
            'name' => 'Live Profile',
            'slug' => 'live-profile',
            'profile_sequence' => 1,
            'profile_status' => 'approved',
            'description' => 'Real profile copy',
            'age' => 25,
            'phone' => '+61400111222',
        ]);

        $demoUser = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $demoUser->id,
            'name' => 'Sample Demo Escort',
            'slug' => 'sample-demo-escort',
            'profile_sequence' => 1,
            'profile_status' => 'approved',
            'description' => 'Demo profile for review only.',
            'introduction_line' => 'Sample listing',
            'profile_text' => 'Sample data for layout preview only.',
            'age' => 25,
        ]);

        $response = $this->get(route('sample-listing'));

        $response->assertRedirect($profile->getEscortUrl());
    }

    public function test_sample_listing_page_redirects_to_search_when_no_demo_safe_profiles_exist(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Approved Profile',
            'slug' => 'approved-profile',
            'profile_sequence' => 1,
            'profile_status' => 'approved',
            'description' => 'Approved profile without demo markers.',
            'age' => 25,
            'phone' => '+61400111222',
        ]);

        $response = $this->get(route('sample-listing'));

        $response->assertRedirect(route('escorts.search'));
    }

    public function test_demo_profile_view_hides_booking_and_appointment_wording(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_PROVIDER]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Demo Safe Profile',
            'slug' => 'demo-safe-profile',
            'profile_sequence' => 1,
            'profile_status' => 'approved',
            'description' => 'Demo profile for review.',
            'introduction_line' => 'Sample listing only',
            'profile_text' => 'Demo data for layout visibility only.',
            'age' => 25,
            'phone' => null,
            'whatsapp' => null,
        ]);

        OnlineUser::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'status' => 'online',
        ]);

        Availability::query()->create([
            'user_id' => $user->id,
            'provider_profile_id' => $profile->id,
            'day' => 'Monday',
            'enabled' => false,
            'all_day' => false,
            'till_late' => false,
            'by_appointment' => true,
        ]);

        $response = $this->get(route('profile.show.no-sequence', [
            'state' => 'au',
            'suburb' => 'australia',
            'slug' => $profile->slug,
        ]));

        $response->assertOk();
        $response->assertDontSee('Send booking enquiry');
        $response->assertDontSee('By appointment');
        $response->assertSee('Unavailable');
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

    public function test_credit_usage_and_expiry_policy_page_loads(): void
    {
        AntiSpamPolicy::query()->create([
            'title' => 'Credit Usage and Expiry Policy',
            'content' => '<p>Credit usage content here.</p>',
            'is_active' => true,
        ]);

        $response = $this->get(route('credit-usage-and-expiry-policy'));

        $response->assertOk();
        $response->assertSeeText('Credit Usage and Expiry Policy');
    }

    public function test_content_moderation_policy_page_loads(): void
    {
        ContentModerationPolicy::query()->create([
            'title' => 'Content Moderation Policy',
            'content' => '<p>Moderation content here.</p>',
            'is_active' => true,
        ]);

        $response = $this->get(route('content-moderation-policy'));

        $response->assertOk();
        $response->assertSeeText('Content Moderation Policy');
    }

    public function test_age_and_consent_policy_page_loads(): void
    {
        AgeAndConsentPolicy::query()->create([
            'title' => 'Age and Consent Policy',
            'content' => '<p>Age and consent content here.</p>',
            'is_active' => true,
        ]);

        $response = $this->get(route('age-and-consent-policy'));

        $response->assertOk();
        $response->assertSeeText('Age and Consent Policy');
    }
}
