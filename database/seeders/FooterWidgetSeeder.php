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
                    ['label' => 'Escorts', 'url' => '/'],
                    ['label' => 'Naughty corner', 'url' => '/naughty-corner'],
                    ['label' => 'Blog', 'url' => '/blog'],
                ],
                'advertisers_heading' => 'Advertisers',
                'advertisers_links' => [
                    ['label' => 'Create Profile', 'url' => '/signup'],
                    ['label' => 'Provider Login', 'url' => '/signin'],
                    ['label' => 'Membership Plans', 'url' => '/membership'],
                    ['label' => 'Pricing & Refunds', 'url' => '/refund-policy'],
                ],
                'legal_heading' => 'Legal & Help',
                'legal_links' => [
                    ['label' => 'FAQ', 'url' => '/faq'],
                    ['label' => 'Contact Us', 'url' => '/contact-us'],
                    ['label' => 'Terms & Conditions', 'url' => '/terms-and-conditions'],
                    ['label' => 'Privacy Policy', 'url' => '/privacy-policy'],
                    ['label' => 'Anti-Spam Policy', 'url' => '/anti-spam-policy'],
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
