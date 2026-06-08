<?php

namespace Database\Seeders;

use App\Models\FooterWidget;
use Illuminate\Database\Seeder;

class FooterWidgetSeeder extends Seeder
{
    public function run(): void
    {
        FooterWidget::updateOrCreate(
            ['id' => 1],
            [
                'brand_description' => 'Australia’s independent adult directory for discovery, profile promotion, and secure advertiser management.',
                'badges' => [
                    ['label' => '18+ Adults Only'],
                    ['label' => 'Verified Listings'],
                    ['label' => 'Privacy First'],
                ],
                'navigation_heading' => 'Navigation',
                'navigation_links' => [
                    ['label' => 'Home', 'url' => '/'],
                    ['label' => 'About', 'url' => '/about-us'],
                    ['label' => 'Contact/Support', 'url' => '/contact-us'],
                    ['label' => 'Browse Listings', 'url' => '/escorts/search'],
                    ['label' => 'Sample Listing', 'url' => '/sample-listing'],
                ],
                'advertisers_heading' => 'Advertisers',
                'advertisers_links' => [
                    ['label' => 'Advertiser registration', 'url' => '/signup'],
                    ['label' => 'Advertiser login', 'url' => '/signin'],
                    ['label' => 'Pricing/credit packages', 'url' => '/pricing'],
                    ['label' => 'How credits work', 'url' => '/how-credits-work'],
                ],
                'legal_heading' => 'Legal',
                'legal_links' => [
                    ['label' => 'Terms and conditions', 'url' => '/terms-and-conditions'],
                    ['label' => 'Privacy Policy', 'url' => '/privacy-policy'],
                    ['label' => 'Refund policy', 'url' => '/refund-policy'],
                    ['label' => 'Contact/support', 'url' => '/contact-us'],
                    ['label' => 'Credit usage and expiry policy', 'url' => '/credit-usage-and-expiry-policy'],
                    ['label' => 'Content Moderation Policy', 'url' => '/content-moderation-policy'],
                    ['label' => 'Report a Listing', 'url' => '/report-a-listing'],
                    ['label' => 'Age and Consent Policy', 'url' => '/age-and-consent-policy'],
                    ['label' => 'Prohibited content/services policy', 'url' => '/prohibited-content-policy'],
                    ['label' => 'Complaints/contact page', 'url' => '/complaints-contact'],
                ],
                'instagram_url' => '/contact-us',
                'twitter_url' => '/contact-us',
                'facebook_url' => '/contact-us',
                'footer_background_color' => null,
                'footer_height' => null,
                'footer_width' => null,
                'enable_brand_widget' => true,
                'enable_navigation_widget' => true,
                'enable_advertisers_widget' => true,
                'enable_legal_widget' => true,
                'enable_promo_section' => true,
                'promo_heading' => 'Want more visibility and calls?',
                'promo_description' => 'Promote your profile with VIP and Diamond plans to reach more users in high-traffic placements.',
                'promo_button_one_label' => 'View Plans',
                'promo_button_one_url' => '/membership',
                'promo_button_two_label' => 'Create Listing',
                'promo_button_two_url' => '/signup',
                'is_active' => true,
            ],
        );
    }
}
