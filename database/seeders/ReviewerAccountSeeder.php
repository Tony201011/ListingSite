<?php

namespace Database\Seeders;

use App\Models\CreditLog;
use App\Models\ProviderListing;
use App\Models\ProviderProfile;
use App\Models\PurchaseTransaction;
use App\Models\SiteSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ReviewerAccountSeeder extends Seeder
{
    /**
     * Reviewer account credentials.
     * The password must be changed after the first deployment to a unique secure value.
     */
    public const EMAIL = 'reviewer@example.com';

    public const PASSWORD = 'Review@2024!';

    public function run(): void
    {
        // Create or update the reviewer user account
        /** @var User $reviewer */
        $reviewer = User::withTrashed()->firstOrCreate(
            ['email' => self::EMAIL],
            [
                'name'              => 'Demo Reviewer',
                'role'              => User::ROLE_REVIEWER,
                'password'          => bcrypt(self::PASSWORD),
                'email_verified_at' => now(),
                'is_blocked'        => false,
            ]
        );

        // Restore if soft-deleted
        if ($reviewer->trashed()) {
            $reviewer->restore();
        }

        $reviewer->update([
            'role'              => User::ROLE_REVIEWER,
            'email_verified_at' => $reviewer->email_verified_at ?? now(),
            'is_blocked'        => false,
        ]);

        // Create a demo provider profile for the reviewer so dashboard pages render correctly
        $freeListingDays = (int) (SiteSetting::getAdTierSettings()['free_listing_days'] ?? 21);

        /** @var ProviderProfile $profile */
        $profile = ProviderProfile::firstOrCreate(
            ['user_id' => $reviewer->id, 'name' => 'Demo Profile (Reviewer)'],
            [
                'slug'                   => 'demo-profile-reviewer',
                'profile_sequence'       => 1,
                'age'                    => 25,
                'description'            => 'This is a sample profile used for payment processor review. No real personal data is shown.',
                'introduction_line'      => 'Sample Advertiser Profile',
                'profile_text'           => 'This is a demonstration profile. All data shown here is fictitious and for review purposes only.',
                'availability'           => 'incalls-and-outcalls',
                'suburb'                 => 'Sydney CBD',
                'is_blocked'             => true,  // Hidden from public listings
                'free_listing_expires_at' => now()->addDays($freeListingDays),
            ]
        );

        // Create a demo listing for the reviewer profile
        ProviderListing::firstOrCreate(
            ['user_id' => $reviewer->id, 'provider_profile_id' => $profile->id],
            [
                'is_live'   => false,
                'is_active' => false,
            ]
        );

        // Seed sample credit transaction history so credit-history page shows demo data
        if (CreditLog::where('user_id', $reviewer->id)->count() === 0) {
            $balance = 50;
            $sampleLogs = [
                ['type' => 'credit',  'transaction_type' => 'purchase',     'amount' => 50,  'description' => 'Initial credit purchase — demo data',   'days' => 30],
                ['type' => 'debit',   'transaction_type' => 'daily_charge',  'amount' => -1,  'description' => 'Daily listing fee — demo data',          'days' => 25],
                ['type' => 'debit',   'transaction_type' => 'daily_charge',  'amount' => -1,  'description' => 'Daily listing fee — demo data',          'days' => 24],
                ['type' => 'debit',   'transaction_type' => 'daily_charge',  'amount' => -1,  'description' => 'Daily listing fee — demo data',          'days' => 23],
                ['type' => 'credit',  'transaction_type' => 'bonus',         'amount' => 5,   'description' => 'Bonus credits — demo data',              'days' => 20],
                ['type' => 'debit',   'transaction_type' => 'daily_charge',  'amount' => -1,  'description' => 'Daily listing fee — demo data',          'days' => 15],
                ['type' => 'debit',   'transaction_type' => 'daily_charge',  'amount' => -1,  'description' => 'Daily listing fee — demo data',          'days' => 10],
                ['type' => 'debit',   'transaction_type' => 'daily_charge',  'amount' => -1,  'description' => 'Daily listing fee — demo data',          'days' => 5],
            ];

            foreach ($sampleLogs as $log) {
                $balance += $log['amount'];
                CreditLog::create([
                    'user_id'          => $reviewer->id,
                    'amount'           => $log['amount'],
                    'balance_after'    => max(0, $balance),
                    'type'             => $log['type'],
                    'transaction_type' => $log['transaction_type'],
                    'status'           => 'completed',
                    'description'      => $log['description'],
                    'created_at'       => now()->subDays($log['days']),
                    'updated_at'       => now()->subDays($log['days']),
                ]);
            }

            $reviewer->update(['credits' => max(0, $balance)]);
        }

        // Seed sample purchase transactions so purchase-history page shows demo data
        if (PurchaseTransaction::where('user_id', $reviewer->id)->count() === 0) {
            $sampleTransactions = [
                [
                    'credits'       => 50,
                    'bonus_credits' => 0,
                    'amount'        => '49.00',
                    'currency'      => 'AUD',
                    'status'        => 'paid',
                    'invoice_name'  => 'Demo Reviewer',
                    'provider'      => 'stripe',
                    'paid_at'       => now()->subDays(30),
                ],
                [
                    'credits'       => 100,
                    'bonus_credits' => 10,
                    'amount'        => '89.00',
                    'currency'      => 'AUD',
                    'status'        => 'paid',
                    'invoice_name'  => 'Demo Reviewer',
                    'provider'      => 'stripe',
                    'paid_at'       => now()->subDays(10),
                ],
            ];

            foreach ($sampleTransactions as $tx) {
                PurchaseTransaction::create(array_merge($tx, [
                    'user_id'             => $reviewer->id,
                    'provider_profile_id' => $profile->id,
                    'provider_checkout_id' => 'demo_' . Str::random(16),
                ]));
            }
        }

        $this->command->info('Reviewer account ready: ' . self::EMAIL);
        $this->command->warn('⚠  Change the reviewer password after deployment via: php artisan reviewer:reset-password');
    }
}
