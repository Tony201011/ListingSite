<?php

namespace Database\Seeders;

use App\Models\PricingPage;
use Illuminate\Database\Seeder;

class PricingPageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $introContent = <<<'HTML'
<p>We don&#039;t believe in basic, pro and premium packages. Every babe gets the same features. Just one credit for every day you advertise.</p>
<p>Not advertising, taking a break, or hiding your profile? No charge, no worries! You can still upload new pictures and update your profile content without paying extra. On the days your profile is offline, you don&#039;t pay &mdash; you only pay when your profile is online.</p>
<p><strong>One credit for every day your profile is online, simple and fair for all.</strong></p>
<p>This includes:</p>
<ul>
    <li>2 x daily Available NOW (2 x 2 hours)</li>
    <li>2 x daily Online NOW (2 x 30 mins)</li>
    <li>Unlimited photos &amp; videos</li>
    <li>Unlimited touring profiles</li>
    <li>Daily Twitter promotions</li>
    <li>Your short profile URL</li>
</ul>
HTML;

        PricingPage::updateOrCreate(
            [
                'title' => 'Pricing',
            ],
            [
                'subtitle' => 'One credit for every day your profile is online, simple and fair for all.',
                'intro_content' => $introContent,
                'packages_title' => 'Packages',
                'packages_content' => '<p>You can purchase your credits in the following packages:</p><table><thead><tr><th>Credits</th><th>Total Price</th><th style="text-align:right;">Price per credit</th></tr></thead><tbody><tr><td>7</td><td><strong>10 AUD $</strong></td><td style="text-align:right;">AUD $1.43</td></tr><tr><td>30</td><td><strong>35 AUD $</strong></td><td style="text-align:right;">AUD $1.17</td></tr><tr><td>60</td><td><strong>65 AUD $</strong></td><td style="text-align:right;">AUD $1.08</td></tr><tr><td>120</td><td><strong>120 AUD $</strong></td><td style="text-align:right;">AUD $1.00</td></tr><tr><td>180</td><td><strong>160 AUD $</strong></td><td style="text-align:right;">AUD $0.89</td></tr></tbody></table>',
                'is_active' => true,
            ],
        );
    }
}
