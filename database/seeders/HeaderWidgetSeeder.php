<?php

namespace Database\Seeders;

use App\Models\HeaderWidget;
use Illuminate\Database\Seeder;

class HeaderWidgetSeeder extends Seeder
{
    public function run(): void
    {
        HeaderWidget::updateOrCreate(
            ['id' => 1],
            [
                'logo_type' => 'text',
                'logo_path' => null,
                'logo_max_width' => 160,
                'logo_max_height' => 40,
                'header_background_color' => null,
                'header_height' => null,
                'header_width' => null,
                'brand_primary' => 'HOT',
                'brand_accent' => 'ESCORTS',
                'enable_top_bar' => false,
                'top_left_items' => [
                    ['label' => 'Verified advertisers', 'icon' => 'fa-solid fa-shield-heart'],
                    ['label' => 'Australia-wide directory', 'icon' => 'fa-solid fa-location-dot'],
                ],
                'top_right_links' => [
                    ['label' => 'Help', 'url' => route('help')],
                    ['label' => 'Contact/Support', 'url' => route('contact-us')],
                    ['label' => 'Complaints/Contact', 'url' => route('complaints-contact')],
                ],
                'enable_search' => true,
                'show_free_trial_cta' => true,
                'free_trial_cta_text' => 'Get 21 days for free',
                'free_trial_cta_url' => url('/signup'),
                'action_links' => [
                    ['label' => 'Pricing', 'url' => url('/pricing')],
                    ['label' => 'Credit Packages', 'url' => url('/membership')],
                    ['label' => 'Advertiser Login', 'url' => url('/signin')],
                    ['label' => 'Advertiser Register', 'url' => url('/signup')],
                ],
                'main_nav_links' => [
                    ['label' => 'Home', 'url' => url('/')],
                    ['label' => 'About us', 'url' => route('about-us')],
                    ['label' => 'Contact/Support', 'url' => route('contact-us')],
                    ['label' => 'Browse Listings', 'url' => route('escorts.search')],
                    ['label' => 'Sample Listing', 'url' => route('sample-listing')],
                    ['label' => 'Pricing', 'url' => url('/pricing')],
                    ['label' => 'How credits work', 'url' => route('how-credits-work')],
                    ['label' => 'Sign Up', 'url' => url('/signup')],
                    ['label' => 'Sign In', 'url' => url('/signin')],
                ],
                'mobile_extra_links' => [
                    ['label' => 'Contact/Support', 'url' => route('contact-us')],
                    ['label' => 'Report a Listing', 'url' => route('report-a-listing')],
                ],
                'is_active' => true,
            ],
        );
    }
}
