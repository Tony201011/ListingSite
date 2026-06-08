<?php

namespace Database\Seeders;

use App\Models\AntiSpamPolicy;
use Illuminate\Database\Seeder;

class AntiSpamPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $policy = AntiSpamPolicy::query()
            ->whereIn('title', ['Credit Usage and Expiry Policy', 'Anti Spam Policy'])
            ->latest('updated_at')
            ->first() ?? new AntiSpamPolicy;

        $policy->fill([
            'title' => 'Credit Usage and Expiry Policy',
            'content' => '<h2>How credits are used</h2><p>One credit is used for each day an advertiser profile stays live and visible on the platform. If a profile is hidden or paused, credits are not consumed during that period.</p><h3>Daily usage timing</h3><p>Credit deductions are processed daily for active profiles. Advertisers can monitor remaining balance from their account dashboard and top up at any time.</p><h3>Expiry rules</h3><p>Purchased credits remain available until their listed expiry date in the package terms. If no expiry date is stated for a package, credits remain valid and usable while the account is active.</p><h3>Refund and reversal</h3><p>Any approved reversal or refund is handled according to the published refund policy. Used credits are generally non-refundable unless required by law.</p><h3>Support</h3><p>For credit usage disputes or expiry questions, please contact support from the contact/support page with account and transaction details.</p>',
            'is_active' => true,
        ]);

        $policy->save();
    }
}
