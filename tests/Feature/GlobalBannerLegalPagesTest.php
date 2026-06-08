<?php

namespace Tests\Feature;

use App\Filament\Clusters\Settings\Resources\GlobalBanners\GlobalBannerResource;
use App\Models\FooterWidget;
use App\Models\GlobalBanner;
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
}
