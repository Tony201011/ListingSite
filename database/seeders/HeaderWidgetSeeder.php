<?php

namespace Database\Seeders;

use App\Models\HeaderWidget;
use Illuminate\Database\Seeder;

class HeaderWidgetSeeder extends Seeder
{
    public function run(): void
    {
        if (HeaderWidget::query()->exists()) {
            return;
        }

        HeaderWidget::query()->create([
            'logo_type' => 'text',
            'logo_path' => null,
            'logo_max_width' => 160,
            'logo_max_height' => 40,
            'header_background_color' => null,
            'header_height' => null,
            'header_width' => null,
            'brand_primary' => 'HOT',
            'brand_accent' => 'ESCORTS',
            'enable_top_bar' => true,
            'top_left_items' => [
                ['label' => 'Verified advertisers', 'icon' => 'fa-solid fa-shield-heart'],
                ['label' => 'Australia-wide directory', 'icon' => 'fa-solid fa-location-dot'],
            ],
            'top_right_links' => [
                ['label' => 'Follow Alice', 'url' => route('blog')],
                ['label' => 'Help', 'url' => route('help')],
                ['label' => 'Contact', 'url' => route('contact-us')],
            ],
            'enable_search' => true,
            'show_free_trial_cta' => true,
            'free_trial_cta_text' => 'Get 21 days for free',
            'free_trial_cta_url' => url('/signup'),
            'action_links' => [
                ['label' => 'Pricing', 'url' => url('/pricing')],
                ['label' => 'Diamonds', 'url' => url('/purchase-credit')],
                ['label' => 'Superboost', 'url' => url('/purchase-credit')],
                ['label' => 'Add advertisement', 'url' => url('/signup')],
            ],
            'main_nav_links' => [
                ['label' => 'Home', 'url' => url('/')],
                ['label' => 'About us', 'url' => route('about-us')],
                ['label' => 'Pricing', 'url' => url('/pricing')],
                ['label' => 'Escorts', 'url' => url('/')],
                ['label' => 'Naughty corner', 'url' => route('naughty-corner')],
                ['label' => 'Blog', 'url' => route('blog')],
                ['label' => 'Sign Up', 'url' => url('/signup')],
                ['label' => 'Sign In', 'url' => url('/signin')],
            ],
            'mobile_extra_links' => [
                ['label' => 'Contact', 'url' => route('contact-us')],
            ],
            'is_active' => true,
        ]);
    }
}
