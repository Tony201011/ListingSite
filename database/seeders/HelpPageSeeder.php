<?php

namespace Database\Seeders;

use App\Models\HelpPage;
use Illuminate\Database\Seeder;

class HelpPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        HelpPage::updateOrCreate(
            [
                'title' => 'Help',
            ],
            [
                'subtitle' => 'Find quick support links for account, profile, and billing related questions.',
                'content' => '<h2>Popular Help Topics</h2><ul><li>How to create and verify your profile</li><li>How to update photos, rates, and availability</li><li>How credits and pricing packages work</li><li>How to hide, pause, or reactivate your listing</li></ul><h2>Need detailed answers?</h2><p>Visit our FAQ page for complete answers to common questions.</p><p><a href="/faq">Go to FAQ</a></p><h2>Still need help?</h2><p>Contact our support team and we\'ll assist you as soon as possible.</p><p><a href="/contact-us">Contact Support</a></p>',
                'is_active' => true,
            ],
        );
    }
}
