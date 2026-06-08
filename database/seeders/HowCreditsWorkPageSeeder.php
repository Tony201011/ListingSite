<?php

namespace Database\Seeders;

use App\Models\HowCreditsWorkPage;
use Illuminate\Database\Seeder;

class HowCreditsWorkPageSeeder extends Seeder
{
    public function run(): void
    {
        HowCreditsWorkPage::updateOrCreate(
            [
                'title' => 'How Credits Work',
            ],
            [
                'subtitle' => 'Learn how credits are used to keep your listing live and visible on the platform.',
                'content' => '<h2>One credit per day your listing is online</h2><p>Credits are only used when an advertiser profile is live and visible on the platform. If a profile is hidden, paused, or offline, no credits are deducted during that period.</p><h2>What each credit includes</h2><ul><li>Daily listing visibility while your profile is online</li><li>Available Now and Online Now status tools</li><li>Unlimited profile updates, photos, and videos</li><li>Access to touring profiles and short profile URLs</li></ul><h2>Buying and tracking credits</h2><p>Advertisers can purchase credits in packages and monitor usage from their account dashboard. Package pricing and inclusions are published on the pricing page.</p><h2>Expiry and support</h2><p>Credit expiry rules are explained in the Credit Usage and Expiry Policy. If you need help with balances, expiry dates, or charges, please contact support.</p><p><a href="/pricing">View Pricing</a> · <a href="/credit-usage-and-expiry-policy">Read Credit Usage &amp; Expiry Policy</a> · <a href="/contact-us">Contact Support</a></p>',
                'is_active' => true,
            ],
        );
    }
}
