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
            'content' => '<h2>Scope of credits and platform payments</h2><p>Advertisers purchase prepaid advertising credits for profile visibility and promotional listing features.</p><p>The platform does not process bookings, deposits, appointment payments, escort payments, or payments between visitors and advertisers.</p><h2>Main credit rules</h2><ul><li>1 credit keeps one approved profile visible for one day.</li><li>Credits are not deducted while a profile is hidden, suspended, or under review.</li><li>If the credit balance reaches zero, the profile is paused automatically.</li><li>Used credits are not refundable.</li><li>Unused credits may be handled according to the refund policy.</li></ul><h3>Daily usage timing</h3><p>Credit deductions are processed daily for active and approved profiles. Advertisers can monitor remaining balance from their account dashboard and top up at any time.</p><h3>Support</h3><p>For credit usage disputes or expiry questions, please contact support from the contact/support page with account and transaction details.</p>',
            'is_active' => true,
        ]);

        $policy->save();
    }
}
