<?php

// database/seeders/MetaDescriptionSeeder.php

namespace Database\Seeders;

use App\Models\MetaDescription;
use Illuminate\Database\Seeder;

class MetaDescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $metaDescriptions = [
            // Public frontend pages
            [
                'page_name' => 'home',
                'meta_description' => 'Discover verified listings, browse profiles, and connect with providers quickly from our premium directory homepage.',
                'is_active' => true,
            ],
            [
                'page_name' => 'advanced-search',
                'meta_description' => 'Use advanced filters to find the right provider by location, category, availability, and profile details.',
                'is_active' => true,
            ],
            [
                'page_name' => 'terms-and-conditions',
                'meta_description' => 'Read our terms and conditions to understand platform rules, responsibilities, and service usage requirements.',
                'is_active' => true,
            ],
            [
                'page_name' => 'privacy-policy',
                'meta_description' => 'Learn how we collect, use, and protect your data through our transparent privacy policy.',
                'is_active' => true,
            ],
            [
                'page_name' => 'refund-policy',
                'meta_description' => 'Review refund eligibility, timelines, and the process for requesting a refund.',
                'is_active' => true,
            ],
            [
                'page_name' => 'anti-spam-policy',
                'meta_description' => 'See how our anti-spam policy helps protect users from abuse, unwanted messages, and harmful content.',
                'is_active' => true,
            ],
            [
                'page_name' => 'about-us',
                'meta_description' => 'Learn about our mission, values, and commitment to building a trusted listing experience.',
                'is_active' => true,
            ],
            [
                'page_name' => 'contact-us',
                'meta_description' => 'Contact our team for support, account assistance, and general inquiries.',
                'is_active' => true,
            ],
            [
                'page_name' => 'faq',
                'meta_description' => 'Find answers to common questions about accounts, listings, billing, and support.',
                'is_active' => true,
            ],
            [
                'page_name' => 'help',
                'meta_description' => 'Access help resources and guides to get the most from your account and listings.',
                'is_active' => true,
            ],
            [
                'page_name' => 'naughty-corner',
                'meta_description' => 'Explore curated content and updates from the Naughty Corner section.',
                'is_active' => true,
            ],
            [
                'page_name' => 'membership',
                'meta_description' => 'Compare membership options, features, and benefits to choose the plan that suits you best.',
                'is_active' => true,
            ],
            [
                'page_name' => 'pricing',
                'meta_description' => 'Review current pricing plans, inclusions, and package details before subscribing.',
                'is_active' => true,
            ],
            [
                'page_name' => 'blog',
                'meta_description' => 'Read the latest news, updates, and helpful articles from our blog.',
                'is_active' => true,
            ],
            [
                'page_name' => 'profile',
                'meta_description' => 'View provider profile details, services, and contact information.',
                'is_active' => true,
            ],
            [
                'page_name' => 'signin',
                'meta_description' => 'Sign in to access your account, manage your profile, and update your listings.',
                'is_active' => true,
            ],
            [
                'page_name' => 'signup',
                'meta_description' => 'Create your account to join the platform, publish your profile, and reach more users.',
                'is_active' => true,
            ],
            [
                'page_name' => 'reset-password',
                'meta_description' => 'Reset your password securely to regain access to your account.',
                'is_active' => true,
            ],
        ];

        // Clear existing records
        MetaDescription::truncate();

        // Insert new records
        foreach ($metaDescriptions as $description) {
            MetaDescription::create($description);
        }

        $this->command->info('Meta Descriptions seeded successfully!');
    }
}
