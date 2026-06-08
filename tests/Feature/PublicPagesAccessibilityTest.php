<?php

namespace Tests\Feature;

use App\Models\FooterWidget;
use App\Models\HeaderWidget;
use Database\Seeders\AboutUsPageSeeder;
use Database\Seeders\AgeAndConsentPolicySeeder;
use Database\Seeders\AntiSpamPolicySeeder;
use Database\Seeders\ContactUsPageSeeder;
use Database\Seeders\ContentModerationPolicySeeder;
use Database\Seeders\FooterWidgetSeeder;
use Database\Seeders\HeaderWidgetSeeder;
use Database\Seeders\HelpPageSeeder;
use Database\Seeders\HowCreditsWorkPageSeeder;
use Database\Seeders\PricingPageSeeder;
use Database\Seeders\PrivacyPolicySeeder;
use Database\Seeders\ProhibitedContentPolicySeeder;
use Database\Seeders\RefundPolicySeeder;
use Database\Seeders\ReportAListingPageSeeder;
use Database\Seeders\TermConditionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesAccessibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_public_routes_are_live_and_accessible(): void
    {
        $this->seed([
            AboutUsPageSeeder::class,
            ContactUsPageSeeder::class,
            HelpPageSeeder::class,
            HowCreditsWorkPageSeeder::class,
            PricingPageSeeder::class,
            TermConditionSeeder::class,
            PrivacyPolicySeeder::class,
            RefundPolicySeeder::class,
            AntiSpamPolicySeeder::class,
            ContentModerationPolicySeeder::class,
            AgeAndConsentPolicySeeder::class,
            ProhibitedContentPolicySeeder::class,
            ReportAListingPageSeeder::class,
            HeaderWidgetSeeder::class,
            FooterWidgetSeeder::class,
        ]);

        $this->get('/')->assertOk();
        $this->get('/about-us')->assertOk();
        $this->get('/contact-us')->assertOk();
        $this->get('/complaints-contact')->assertOk();
        $this->get('/escorts/search')->assertOk();
        $this->get('/sample-listing')->assertRedirect(route('escorts.search'));
        $this->get('/signup')->assertOk();
        $this->get('/signin')->assertOk();
        $this->get('/pricing')->assertOk();
        $this->get('/membership')->assertOk();
        $this->get('/how-credits-work')->assertOk();
        $this->get('/terms-and-conditions')->assertOk();
        $this->get('/privacy-policy')->assertOk();
        $this->get('/refund-policy')->assertOk();
        $this->get('/credit-usage-and-expiry-policy')->assertOk();
        $this->get('/content-moderation-policy')->assertOk();
        $this->get('/age-and-consent-policy')->assertOk();
        $this->get('/prohibited-content-policy')->assertOk();
        $this->get('/report-a-listing')->assertOk();
    }

    public function test_public_page_seeders_are_idempotent_and_no_duplicate_links_are_created(): void
    {
        $this->seed([
            AboutUsPageSeeder::class,
            ContactUsPageSeeder::class,
            AntiSpamPolicySeeder::class,
            HowCreditsWorkPageSeeder::class,
            HeaderWidgetSeeder::class,
            FooterWidgetSeeder::class,
        ]);

        $this->seed([
            AboutUsPageSeeder::class,
            ContactUsPageSeeder::class,
            AntiSpamPolicySeeder::class,
            HowCreditsWorkPageSeeder::class,
            HeaderWidgetSeeder::class,
            FooterWidgetSeeder::class,
        ]);

        $this->assertDatabaseCount('about_us_pages', 1);
        $this->assertDatabaseCount('contact_us_pages', 1);
        $this->assertDatabaseCount('anti_spam_policies', 1);
        $this->assertDatabaseCount('how_credits_work_pages', 1);
        $this->assertDatabaseCount('header_widgets', 1);
        $this->assertDatabaseCount('footer_widgets', 1);

        $headerLinkUrls = collect(HeaderWidget::query()->firstOrFail()->main_nav_links)
            ->pluck('url')
            ->filter()
            ->values();
        $this->assertSame($headerLinkUrls->count(), $headerLinkUrls->unique()->count());

        $footerLegalUrls = collect(FooterWidget::query()->firstOrFail()->legal_links)
            ->pluck('url')
            ->filter()
            ->values();
        $this->assertSame($footerLegalUrls->count(), $footerLegalUrls->unique()->count());

        $this->assertContains('/contact-us', $headerLinkUrls->all());

        $footerNavigationUrls = collect(FooterWidget::query()->firstOrFail()->navigation_links)
            ->pluck('url')
            ->filter()
            ->values();
        $this->assertSame($footerNavigationUrls->count(), $footerNavigationUrls->unique()->count());
        $this->assertContains('/contact-us', $footerNavigationUrls->all());
    }
}
