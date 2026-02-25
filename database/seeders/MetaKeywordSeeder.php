<?php
// database/seeders/MetaKeywordSeeder.php

namespace Database\Seeders;

use App\Models\MetaKeyword;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MetaKeywordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $metaKeywords = [
            // Settings Pages
            [
                'page_name' => 's3-bucket',
                'meta_keyword' => 's3, bucket, storage, cloud, aws, amazon s3, file upload, cloud storage, media storage, s3 settings, bucket configuration',
                'is_active' => true,
            ],
            [
                'page_name' => 'smtp-settings',
                'meta_keyword' => 'smtp, email, mail settings, email configuration, smtp server, email delivery, mail server, email settings, smtp settings',
                'is_active' => true,
            ],
            [
                'page_name' => 'social-login',
                'meta_keyword' => 'social login, oauth, authentication, google login, facebook login, github login, social authentication, login settings',
                'is_active' => true,
            ],

            // Policy Pages
            [
                'page_name' => 'terms-conditions',
                'meta_keyword' => 'terms, conditions, terms of service, terms and conditions, user agreement, legal terms, service terms, website terms',
                'is_active' => true,
            ],
            [
                'page_name' => 'privacy-policy',
                'meta_keyword' => 'privacy, privacy policy, data protection, gdpr, privacy settings, data privacy, personal data, privacy terms',
                'is_active' => true,
            ],
            [
                'page_name' => 'refund-policy',
                'meta_keyword' => 'refund, refund policy, money back, cancellation, return policy, refund terms, refund process, refund request',
                'is_active' => true,
            ],
            [
                'page_name' => 'anti-spam',
                'meta_keyword' => 'anti-spam, spam policy, spam prevention, email spam, spam protection, anti-spam policy, spam filtering',
                'is_active' => true,
            ],

            // Content Pages
            [
                'page_name' => 'home',
                'meta_keyword' => 'home, dashboard, welcome, main page, homepage, index, application home, main dashboard',
                'is_active' => true,
            ],
            [
                'page_name' => 'about',
                'meta_keyword' => 'about, about us, company, information, about page, our story, team, mission, vision',
                'is_active' => true,
            ],
            [
                'page_name' => 'contact',
                'meta_keyword' => 'contact, contact us, support, help, get in touch, customer support, contact form, email us',
                'is_active' => true,
            ],
            [
                'page_name' => 'faq',
                'meta_keyword' => 'faq, frequently asked questions, help, questions, answers, support, knowledge base, help center',
                'is_active' => true,
            ],

            // Listing Pages
            [
                'page_name' => 'provider-listing',
                'meta_keyword' => 'providers, service providers, listing, provider list, service listing, providers directory, provider catalog',
                'is_active' => true,
            ],
            [
                'page_name' => 'categories',
                'meta_keyword' => 'categories, category list, classification, groups, types, category listing, service categories',
                'is_active' => true,
            ],
            [
                'page_name' => 'gender-tabs',
                'meta_keyword' => 'gender, gender tabs, male, female, other, gender selection, gender options, gender categories',
                'is_active' => true,
            ],
            [
                'page_name' => 'content-listings',
                'meta_keyword' => 'content, listings, content list, content management, content listing, published content, content directory',
                'is_active' => true,
            ],

            // Additional Pages
            [
                'page_name' => 'dashboard',
                'meta_keyword' => 'dashboard, admin dashboard, control panel, admin panel, statistics, analytics, overview',
                'is_active' => true,
            ],
            [
                'page_name' => 'profile',
                'meta_keyword' => 'profile, user profile, account, personal information, profile settings, user account, profile page',
                'is_active' => true,
            ],
            [
                'page_name' => 'settings',
                'meta_keyword' => 'settings, configuration, preferences, app settings, system settings, general settings, options',
                'is_active' => true,
            ],
            [
                'page_name' => 'notifications',
                'meta_keyword' => 'notifications, alerts, updates, notification settings, email notifications, push notifications',
                'is_active' => true,
            ],
        ];

        // Clear existing records
        MetaKeyword::truncate();

        // Insert new records
        foreach ($metaKeywords as $keyword) {
            MetaKeyword::create($keyword);
        }

        $this->command->info('Meta Keywords seeded successfully!');
    }
}
