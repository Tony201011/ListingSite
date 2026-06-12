<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('pricing_pages')) {
            return;
        }

        $oldIntroContent = '<p>Advertisers purchase prepaid advertising credits. Credits are used for profile visibility and promotional listing features.</p><p>All payments on this platform are exclusively for purchasing advertising credits and promotional listing packages. No payments are processed between visitors and advertisers.</p><h2>Main credit rules</h2><ul><li>1 credit keeps one approved profile visible for one day.</li><li>Credits are not deducted while a profile is hidden, suspended, or under review.</li><li>If the credit balance reaches zero, the profile is paused automatically.</li><li>Used credits are not refundable.</li><li>Unused credits may be handled according to the refund policy.</li></ul><p>This includes:</p><ul><li>2 x daily Available NOW (2 x 2 hours)</li><li>2 x daily Online NOW (2 x 30 mins)</li><li>Unlimited photos &amp; videos</li><li>Unlimited touring profiles</li><li>Daily Twitter promotions</li><li>Your short profile URL</li></ul>';
        $newIntroContent = <<<'HTML'
<p>We don't believe in basic, pro and premium packages. Every babe gets the same features. Just one credit for every day you advertise.</p>
<p>Not advertising, taking a break, or hiding your profile? No charge, no worries! You can still upload new pictures and update your profile content without paying extra. On the days your profile is offline, you don't pay &mdash; you only pay when your profile is online.</p>
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

        DB::table('pricing_pages')
            ->where('title', 'Pricing')
            ->where(function ($query) use ($oldIntroContent): void {
                $query->whereNull('intro_content')
                    ->orWhere('intro_content', $oldIntroContent);
            })
            ->update([
                'subtitle' => 'One credit for every day your profile is online, simple and fair for all.',
                'intro_content' => $newIntroContent,
                'updated_at' => now(),
            ]);
    }

    public function down(): void
    {
        if (! Schema::hasTable('pricing_pages')) {
            return;
        }

        $oldIntroContent = '<p>Advertisers purchase prepaid advertising credits. Credits are used for profile visibility and promotional listing features.</p><p>All payments on this platform are exclusively for purchasing advertising credits and promotional listing packages. No payments are processed between visitors and advertisers.</p><h2>Main credit rules</h2><ul><li>1 credit keeps one approved profile visible for one day.</li><li>Credits are not deducted while a profile is hidden, suspended, or under review.</li><li>If the credit balance reaches zero, the profile is paused automatically.</li><li>Used credits are not refundable.</li><li>Unused credits may be handled according to the refund policy.</li></ul><p>This includes:</p><ul><li>2 x daily Available NOW (2 x 2 hours)</li><li>2 x daily Online NOW (2 x 30 mins)</li><li>Unlimited photos &amp; videos</li><li>Unlimited touring profiles</li><li>Daily Twitter promotions</li><li>Your short profile URL</li></ul>';
        $newIntroContent = <<<'HTML'
<p>We don't believe in basic, pro and premium packages. Every babe gets the same features. Just one credit for every day you advertise.</p>
<p>Not advertising, taking a break, or hiding your profile? No charge, no worries! You can still upload new pictures and update your profile content without paying extra. On the days your profile is offline, you don't pay &mdash; you only pay when your profile is online.</p>
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

        DB::table('pricing_pages')
            ->where('title', 'Pricing')
            ->where('intro_content', $newIntroContent)
            ->update([
                'subtitle' => 'Simple and fair credits pricing for all profiles.',
                'intro_content' => $oldIntroContent,
                'updated_at' => now(),
            ]);
    }
};
