<?php

namespace Tests\Feature;

use App\Filament\Clusters\Settings\Resources\GlobalBanners\GlobalBannerResource;
use App\Models\AgeAndConsentPolicy;
use App\Models\ContentModerationPolicy;
use App\Models\ContactUsPage;
use App\Models\FooterWidget;
use App\Models\GlobalBanner;
use App\Models\PrivacyPolicy;
use App\Models\ProhibitedContentPolicy;
use App\Models\RefundPolicy;
use App\Models\ReportAListingPage;
use App\Models\TermCondition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GlobalBannerLegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_global_banner_page_options_include_all_footer_legal_pages(): void
    {
        FooterWidget::create([
            'brand_description' => 'Legal links config',
            'legal_heading' => 'Legal',
            'legal_links' => [
                ['label' => 'Terms & Conditions', 'url' => '/terms-and-conditions'],
                ['label' => 'Privacy Policy', 'url' => '/privacy-policy'],
                ['label' => 'Refund Policy', 'url' => '/refund-policy'],
                ['label' => 'Contact/Support', 'url' => '/contact-us'],
                ['label' => 'Content Moderation Policy', 'url' => '/content-moderation-policy'],
                ['label' => 'Report a Listing', 'url' => '/report-a-listing'],
                ['label' => 'Age and Consent Policy', 'url' => '/age-and-consent-policy'],
                ['label' => 'Prohibited Content/Services Policy', 'url' => '/prohibited-content-policy'],
            ],
            'is_active' => true,
        ]);

        $options = GlobalBannerResource::getPageOptions();

        $this->assertSame('Terms & Conditions', $options['terms-and-conditions'] ?? null);
        $this->assertSame('Privacy Policy', $options['privacy-policy'] ?? null);
        $this->assertSame('Refund Policy', $options['refund-policy'] ?? null);
        $this->assertSame('Contact/Support', $options['contact-us'] ?? null);
        $this->assertSame('Content Moderation Policy', $options['content-moderation-policy'] ?? null);
        $this->assertSame('Report a Listing', $options['report-a-listing'] ?? null);
        $this->assertSame('Age and Consent Policy', $options['age-and-consent-policy'] ?? null);
        $this->assertSame('Prohibited Content/Services Policy', $options['prohibited-content-policy'] ?? null);
    }

    public function test_global_banner_page_options_fall_back_to_active_legal_pages_without_footer_links(): void
    {
        TermCondition::create([
            'title' => 'Terms & Conditions',
            'content' => 'Terms content',
            'is_active' => true,
        ]);

        PrivacyPolicy::create([
            'title' => 'Privacy Policy',
            'content' => 'Privacy content',
            'is_active' => true,
        ]);

        RefundPolicy::create([
            'title' => 'Refund Policy',
            'content' => 'Refund content',
            'is_active' => true,
        ]);

        ContactUsPage::create([
            'title' => 'Contact/Support',
            'subtitle' => 'Support',
            'support_heading' => 'Support',
            'response_time' => '24 hours',
            'support_email' => 'support@example.com',
            'category_label' => 'Support',
            'enable_name_field' => true,
            'enable_email_field' => true,
            'enable_subject_field' => true,
            'enable_message_field' => true,
            'enable_map' => false,
            'is_active' => true,
        ]);

        ContentModerationPolicy::create([
            'title' => 'Content Moderation Policy',
            'content' => 'Moderation content',
            'is_active' => true,
        ]);

        ReportAListingPage::create([
            'title' => 'Report a Listing',
            'content' => 'Report content',
            'is_active' => true,
        ]);

        AgeAndConsentPolicy::create([
            'title' => 'Age and Consent Policy',
            'content' => 'Age content',
            'is_active' => true,
        ]);

        ProhibitedContentPolicy::create([
            'title' => 'Prohibited Content/Services Policy',
            'content' => 'Prohibited content',
            'is_active' => true,
        ]);

        $options = GlobalBannerResource::getPageOptions();

        $this->assertSame('Terms & Conditions', $options['terms-and-conditions'] ?? null);
        $this->assertSame('Privacy Policy', $options['privacy-policy'] ?? null);
        $this->assertSame('Refund Policy', $options['refund-policy'] ?? null);
        $this->assertSame('Contact/Support', $options['contact-us'] ?? null);
        $this->assertSame('Content Moderation Policy', $options['content-moderation-policy'] ?? null);
        $this->assertSame('Report a Listing', $options['report-a-listing'] ?? null);
        $this->assertSame('Age and Consent Policy', $options['age-and-consent-policy'] ?? null);
        $this->assertSame('Prohibited Content/Services Policy', $options['prohibited-content-policy'] ?? null);
    }

    public function test_global_banner_renders_on_all_legal_footer_pages(): void
    {
        GlobalBanner::create([
            'page_keys' => [
                'terms-and-conditions',
                'privacy-policy',
                'refund-policy',
                'contact-us',
                'content-moderation-policy',
                'report-a-listing',
                'age-and-consent-policy',
                'prohibited-content-policy',
            ],
            'banner_image_path' => null,
            'banner_title' => 'Legal Pages Banner',
            'banner_subtitle' => 'Shown on policy pages',
            'is_active' => true,
        ]);

        foreach ([
            'terms-and-conditions',
            'privacy-policy',
            'refund-policy',
            'contact-us',
            'content-moderation-policy',
            'report-a-listing',
            'age-and-consent-policy',
            'prohibited-content-policy',
        ] as $routeName) {
            $this->get(route($routeName))
                ->assertOk()
                ->assertSee('Legal Pages Banner');
        }
    }

    public function test_global_banner_does_not_render_on_unselected_page(): void
    {
        GlobalBanner::create([
            'page_keys' => ['home'],
            'banner_image_path' => null,
            'banner_title' => 'Home Only Banner',
            'banner_subtitle' => 'Shown only on home',
            'is_active' => true,
        ]);

        $this->get(route('home'))
            ->assertOk()
            ->assertSee('Home Only Banner');

        $this->get(route('signin'))
            ->assertOk()
            ->assertDontSee('Home Only Banner');
    }

    public function test_global_banner_normalizes_multi_select_page_state_before_saving(): void
    {
        $banner = GlobalBanner::create([
            'page_keys' => [
                'home' => true,
                'signin' => true,
                'signup' => false,
            ],
            'banner_image_path' => null,
            'banner_title' => 'State Normalization Banner',
            'banner_subtitle' => 'Testing normalized pages',
            'is_active' => true,
        ]);

        $banner->refresh();

        $this->assertSame(['home', 'signin'], $banner->page_keys);
        $this->assertSame('home', $banner->page_key);
    }

    public function test_admin_can_open_global_banner_page_without_errors_when_no_legal_pages_exist(): void
    {
        $admin = User::factory()->create([
            'role' => User::ROLE_ADMIN,
            'email_verified_at' => now(),
            'is_blocked' => false,
        ]);

        $this->actingAs($admin, 'admin')
            ->get('/admin/settings/global-banners')
            ->assertOk();
    }
}
