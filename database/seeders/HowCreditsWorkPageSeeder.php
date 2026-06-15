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
                'content' => '<h2>What your credits are for</h2><p>Advertisers purchase prepaid advertising credits. Credits are used for profile visibility and promotional listing features.</p><p>All payments on this platform are exclusively for purchasing advertising credits and promotional listing packages. The platform does not process bookings, deposits, appointment payments, escort payments, or payments between visitors and advertisers.</p><h2>Main credit rules</h2><ul><li>1 credit keeps one approved profile visible for one day.</li><li>Credits are not deducted while a profile is hidden, suspended, or under review.</li><li>If the credit balance reaches zero, the profile is paused automatically.</li><li>Used credits are not refundable.</li><li>Unused credits may be handled according to the refund policy.</li></ul><h2>What each credit includes</h2><ul><li>Daily listing visibility while your profile is online</li><li>Available Now and Online Now status tools</li><li>Unlimited profile updates, photos, and videos</li><li>Access to touring profiles and short profile URLs</li></ul><h2>Buying and tracking credits</h2><p>Advertisers can purchase credits in packages and monitor usage from their account dashboard. Package pricing and inclusions are published on the pricing page.</p><p><a href="/pricing">View Pricing</a> · <a href="/credit-usage-and-expiry-policy">Read Credit Usage &amp; Expiry Policy</a> · <a href="/contact-us">Contact Support</a></p>',
                'is_active' => true,
            ],
        );
    }
}
