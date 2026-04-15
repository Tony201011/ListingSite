<?php

// database/seeders/MetaKeywordSeeder.php

namespace Database\Seeders;

use App\Models\MetaKeyword;
use Illuminate\Database\Seeder;

class MetaKeywordSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $metaKeywords = [
            [
                'page_name' => 'home',
                'meta_keyword' => 'escorts australia, hot escorts, escort directory, independent escorts, verified profiles, adult services, local escorts, premium listings',
                'is_active' => true,
            ],
            [
                'page_name' => 'advanced-search',
                'meta_keyword' => 'escort search, find escorts, escort filters, suburb search, city escorts, age filter, price filter, nearby escorts',
                'is_active' => true,
            ],
            [
                'page_name' => 'about-us',
                'meta_keyword' => 'about hot escort, escort platform australia, trusted listings, escort community, adult directory team, service mission',
                'is_active' => true,
            ],
            [
                'page_name' => 'contact-us',
                'meta_keyword' => 'contact hot escort, customer support, escort listing support, help desk, contact form, platform assistance',
                'is_active' => true,
            ],
            [
                'page_name' => 'privacy-policy',
                'meta_keyword' => 'privacy policy, data protection, personal data, user privacy, cookies policy, account privacy, information security',
                'is_active' => true,
            ],
            [
                'page_name' => 'refund-policy',
                'meta_keyword' => 'refund policy, billing policy, payment refunds, cancellation terms, credits refund, membership refunds',
                'is_active' => true,
            ],
            [
                'page_name' => 'terms-and-conditions',
                'meta_keyword' => 'terms and conditions, user agreement, legal terms, platform rules, escort listing policies, site terms',
                'is_active' => true,
            ],
            [
                'page_name' => 'anti-spam-policy',
                'meta_keyword' => 'anti spam policy, spam prevention, abuse reporting, fake profile prevention, message safety, content moderation',
                'is_active' => true,
            ],
            [
                'page_name' => 'faq',
                'meta_keyword' => 'escort faq, frequently asked questions, booking help, profile help, account support, payment questions',
                'is_active' => true,
            ],
            [
                'page_name' => 'help',
                'meta_keyword' => 'help center, support guide, escort listing help, account help, profile management help, user support',
                'is_active' => true,
            ],
            [
                'page_name' => 'naughty-corner',
                'meta_keyword' => 'naughty corner, adult blog, sexy stories, mature content, nightlife content, erotic articles',
                'is_active' => true,
            ],
            [
                'page_name' => 'membership',
                'meta_keyword' => 'escort membership, premium membership, listing plans, member benefits, subscription plans, provider membership',
                'is_active' => true,
            ],
            [
                'page_name' => 'pricing',
                'meta_keyword' => 'escort pricing, listing packages, ad pricing, premium listing cost, subscription pricing, plan comparison',
                'is_active' => true,
            ],
            [
                'page_name' => 'blog',
                'meta_keyword' => 'escort blog, dating tips, nightlife guide, adult lifestyle, safety tips, relationship advice',
                'is_active' => true,
            ],
            [
                'page_name' => 'signin',
                'meta_keyword' => 'escort login, member login, provider login, secure sign in, account access, user authentication',
                'is_active' => true,
            ],
            [
                'page_name' => 'signup',
                'meta_keyword' => 'escort signup, join platform, create account, provider registration, member registration, new account',
                'is_active' => true,
            ],
            [
                'page_name' => 'site-password',
                'meta_keyword' => 'site access password, protected access, members only, private content access, secure entry',
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
