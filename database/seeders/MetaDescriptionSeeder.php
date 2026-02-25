<?php
// database/seeders/MetaDescriptionSeeder.php

namespace Database\Seeders;

use App\Models\MetaDescription;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class MetaDescriptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $metaDescriptions = [
            // Settings Pages
            [
                'page_name' => 's3-bucket',
                'meta_description' => 'Configure and manage your Amazon S3 bucket settings for cloud storage, file uploads, and media management. Enable secure and scalable cloud storage for your application with our comprehensive S3 configuration options.',
                'is_active' => true,
            ],
            [
                'page_name' => 'smtp-settings',
                'meta_description' => 'Set up and configure SMTP settings for reliable email delivery. Manage mail servers, ports, encryption, and authentication to ensure seamless communication with your users through email notifications.',
                'is_active' => true,
            ],
            [
                'page_name' => 'social-login',
                'meta_description' => 'Enable social login functionality for your application. Configure OAuth providers including Google, Facebook, GitHub, and more to simplify user authentication and improve user experience.',
                'is_active' => true,
            ],

            // Policy Pages
            [
                'page_name' => 'terms-conditions',
                'meta_description' => 'Read our comprehensive terms and conditions that govern the use of our services. Understand your rights, responsibilities, and the legal agreement between you and our platform.',
                'is_active' => true,
            ],
            [
                'page_name' => 'privacy-policy',
                'meta_description' => 'Learn how we collect, use, and protect your personal data. Our privacy policy outlines our commitment to data protection, GDPR compliance, and your privacy rights when using our services.',
                'is_active' => true,
            ],
            [
                'page_name' => 'refund-policy',
                'meta_description' => 'Understand our refund policy and the process for requesting refunds. Learn about eligibility criteria, timeframes, and how we ensure customer satisfaction with fair refund terms.',
                'is_active' => true,
            ],
            [
                'page_name' => 'anti-spam',
                'meta_description' => 'Learn about our anti-spam policy and how we protect users from unwanted communications. Discover our commitment to maintaining a spam-free environment and protecting user inboxes.',
                'is_active' => true,
            ],

            // Content Pages
            [
                'page_name' => 'home',
                'meta_description' => 'Welcome to your comprehensive application dashboard. Access all your settings, monitor analytics, manage content, and control every aspect of your application from one central location.',
                'is_active' => true,
            ],
            [
                'page_name' => 'about',
                'meta_description' => 'Learn more about our company, our mission to provide exceptional service, and the dedicated team working behind the scenes to make your experience better every day.',
                'is_active' => true,
            ],
            [
                'page_name' => 'contact',
                'meta_description' => 'Get in touch with our support team for any questions or assistance. We are here to help with inquiries, feedback, and support requests to ensure your satisfaction.',
                'is_active' => true,
            ],
            [
                'page_name' => 'faq',
                'meta_description' => 'Find answers to frequently asked questions about our services, features, billing, and technical support. Browse our comprehensive FAQ section for quick solutions to common queries.',
                'is_active' => true,
            ],

            // Listing Pages
            [
                'page_name' => 'provider-listing',
                'meta_description' => 'Browse our comprehensive directory of service providers. Find trusted professionals, compare services, and choose the best provider for your needs from our verified listings.',
                'is_active' => true,
            ],
            [
                'page_name' => 'categories',
                'meta_description' => 'Explore our organized categories to find exactly what you need. Browse through well-structured classifications to discover services, products, and content tailored to your interests.',
                'is_active' => true,
            ],
            [
                'page_name' => 'gender-tabs',
                'meta_description' => 'Navigate through gender-specific content with our intuitive gender tabs. Easily filter and access content based on gender preferences for a personalized browsing experience.',
                'is_active' => true,
            ],
            [
                'page_name' => 'content-listings',
                'meta_description' => 'Discover our curated content listings featuring the best articles, posts, and resources. Browse through organized content to find valuable information and engaging material.',
                'is_active' => true,
            ],

            // Additional Pages
            [
                'page_name' => 'dashboard',
                'meta_description' => 'Access your personalized dashboard to monitor activities, track performance, and manage your account settings. Get a comprehensive overview of your application at a glance.',
                'is_active' => true,
            ],
            [
                'page_name' => 'profile',
                'meta_description' => 'Manage your profile information, update personal details, and customize your account settings. Keep your profile up-to-date for the best experience on our platform.',
                'is_active' => true,
            ],
            [
                'page_name' => 'settings',
                'meta_description' => 'Configure your application preferences and system settings. Customize your experience with comprehensive options for notifications, privacy, and general configuration.',
                'is_active' => true,
            ],
            [
                'page_name' => 'notifications',
                'meta_description' => 'Manage your notification preferences and stay updated with important alerts. Customize how and when you receive updates about activities, messages, and system events.',
                'is_active' => true,
            ],
            [
                'page_name' => 'search',
                'meta_description' => 'Search through our extensive database to find content, services, and information. Use our powerful search functionality to quickly locate exactly what you need.',
                'is_active' => true,
            ],
            [
                'page_name' => 'help-center',
                'meta_description' => 'Visit our help center for comprehensive guides, tutorials, and support resources. Find solutions to common issues and learn how to make the most of our platform.',
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
