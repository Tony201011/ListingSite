<?php

namespace Database\Seeders;

use App\Models\Availability;
use App\Models\AvailableNow;
use App\Models\Category;
use App\Models\CreditLog;
use App\Models\OnlineUser;
use App\Models\ProfileImage;
use App\Models\ProfileMessage;
use App\Models\ProviderListing;
use App\Models\ProviderProfile;
use App\Models\PurchaseTransaction;
use App\Models\Rate;
use App\Models\RateGroup;
use App\Models\SiteSetting;
use App\Models\Tour;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TestAdvertiserAccountSeeder extends Seeder
{
    public const EMAIL = 'test-advertiser@example.com';

    public const PASSWORD = 'Advertiser@12345';

    private const PROFILE_NAME = 'Test Advertiser Profile';

    private const PROFILE_SLUG = 'test-advertiser-profile';

    private const PENDING_PROFILE_NAME = 'Test Advertiser (Pending Review)';

    private const PENDING_PROFILE_SLUG = 'test-advertiser-pending';

    private const PRIMARY_IMAGE_URL = 'https://picsum.photos/seed/demo-advertiser-1/400/400';

    private const DAYS = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    private const RATE_DESCRIPTIONS = ['30 min', '1 hour', '2 hours', 'Overnight'];

    private const INCALL_PRICES = ['$150', '$250', '$400', '$800'];

    private const OUTCALL_PRICES = ['$200', '$300', '$500', '$1000'];

    public function run(): void
    {
        $freeListingDays = (int) (SiteSetting::getAdTierSettings()['free_listing_days'] ?? 21);

        // -----------------------------------------------------------------------
        // 1. User account
        // -----------------------------------------------------------------------
        /** @var User $advertiser */
        $advertiser = User::withTrashed()->firstOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'Test Advertiser',
                'role' => User::ROLE_TEST_ADVERTISER,
                'password' => self::PASSWORD,
                'mobile' => '0400000000',
                'mobile_verified' => true,
                'email_verified_at' => now(),
                'account_status' => 'active',
                'is_blocked' => false,
            ]
        );

        if ($advertiser->trashed()) {
            $advertiser->restore();
        }

        $advertiser->update([
            'name' => 'Test Advertiser',
            'role' => User::ROLE_TEST_ADVERTISER,
            'password' => self::PASSWORD,
            'mobile' => '0400000000',
            'mobile_verified' => true,
            'email_verified_at' => $advertiser->email_verified_at ?? now(),
            'account_status' => 'active',
            'is_blocked' => false,
        ]);

        // -----------------------------------------------------------------------
        // 2. Main approved profile (blocked from public search — demo data only)
        // -----------------------------------------------------------------------
        /** @var ProviderProfile $profile */
        $profile = ProviderProfile::withTrashed()->firstOrNew([
            'user_id' => $advertiser->id,
            'name' => self::PROFILE_NAME,
        ]);

        if ($profile->trashed()) {
            $profile->restore();
        }

        $profile->fill([
            'slug' => self::PROFILE_SLUG,
            'profile_sequence' => 1,
            'age' => 29,
            'description' => '[DEMO] This is a sample advertiser profile used for payment processor review. All content and contact details are fictitious.',
            'introduction_line' => '[DEMO] Sample advertiser profile — QA and sandbox verification only.',
            'profile_text' => '[DEMO] This advertiser account exists only for compliance review purposes. All data shown here is fictitious and must not be used with real customer information.',
            'primary_identity' => $this->resolveCategoryIds('primary-identity', 1),
            'attributes' => $this->resolveCategoryIds('attributes', 3),
            'services_style' => $this->resolveCategoryIds('services-style', 3),
            'services_provided' => $this->resolveCategoryIds('services-you-provide', 3),
            'age_group_id' => $this->resolveCategoryId('age-group'),
            'hair_color_id' => $this->resolveCategoryId('hair-color'),
            'hair_length_id' => $this->resolveCategoryId('hair-length'),
            'ethnicity_id' => $this->resolveCategoryId('ethnicity'),
            'body_type_id' => $this->resolveCategoryId('body-type'),
            'bust_size_id' => $this->resolveCategoryId('bust-size'),
            'your_length_id' => $this->resolveCategoryId('your-length'),
            'availability' => 'incalls-and-outcalls',
            'contact_method' => 'phone-only',
            'phone_contact_preference' => 'accept-calls-sms',
            'time_waster_shield' => 'no',
            'suburb' => 'Sydney CBD',
            'phone' => '0400000000',
            'is_verified' => true,
            'is_blocked' => true,   // Hidden from public search; visible to the account holder
            'profile_status' => 'approved',
            'free_listing_expires_at' => now()->addDays($freeListingDays),
            // Premium tier demo — shows reviewer what featured/banner placements look like
            'is_featured' => true,
            'featured_expires_at' => now()->addDays(30),
            'home_featured_expires_at' => now()->addDays(30),
            'local_banner_expires_at' => now()->addDays(30),
            'home_banner_expires_at' => now()->addDays(15),
            'expires_at' => now()->addMonths(6),
        ]);
        $profile->save();

        // -----------------------------------------------------------------------
        // 3. Pending profile (demonstrates the moderation / pending-review state)
        // -----------------------------------------------------------------------
        /** @var ProviderProfile $pendingProfile */
        $pendingProfile = ProviderProfile::withTrashed()->firstOrNew([
            'user_id' => $advertiser->id,
            'name' => self::PENDING_PROFILE_NAME,
        ]);

        if ($pendingProfile->trashed()) {
            $pendingProfile->restore();
        }

        $pendingProfile->fill([
            'slug' => self::PENDING_PROFILE_SLUG,
            'profile_sequence' => 2,
            'age' => 27,
            'description' => '[DEMO] This profile is awaiting admin review. Created to demonstrate the pending-review moderation status.',
            'introduction_line' => '[DEMO] Profile pending review.',
            'profile_text' => '[DEMO] This is demo content for a profile currently under admin moderation.',
            'primary_identity' => $this->resolveCategoryIds('primary-identity', 1),
            'attributes' => $this->resolveCategoryIds('attributes', 2),
            'services_style' => $this->resolveCategoryIds('services-style', 2),
            'services_provided' => $this->resolveCategoryIds('services-you-provide', 2),
            'age_group_id' => $this->resolveCategoryId('age-group'),
            'hair_color_id' => $this->resolveCategoryId('hair-color'),
            'hair_length_id' => $this->resolveCategoryId('hair-length'),
            'ethnicity_id' => $this->resolveCategoryId('ethnicity'),
            'body_type_id' => $this->resolveCategoryId('body-type'),
            'bust_size_id' => $this->resolveCategoryId('bust-size'),
            'your_length_id' => $this->resolveCategoryId('your-length'),
            'availability' => 'incalls-only',
            'contact_method' => 'phone-only',
            'phone_contact_preference' => 'accept-calls-sms',
            'time_waster_shield' => 'no',
            'suburb' => 'Melbourne CBD',
            'phone' => '0400000001',
            'is_blocked' => true,   // Hidden from public — pending moderation
            'profile_status' => 'pending',
            'free_listing_expires_at' => now()->addDays($freeListingDays),
        ]);
        $pendingProfile->save();

        // -----------------------------------------------------------------------
        // 4. Listings for the main profile (active, paused, and inactive examples)
        // -----------------------------------------------------------------------
        // Active listing — demonstrates an online/live listing
        ProviderListing::updateOrCreate(
            [
                'user_id' => $advertiser->id,
                'provider_profile_id' => $profile->id,
                'title' => 'Test Advertiser — Active Demo Listing',
            ],
            [
                'is_live' => true,
                'is_active' => true,
                'is_vip' => false,
            ]
        );

        // Paused listing — demonstrates a listing that is active but currently paused
        ProviderListing::updateOrCreate(
            [
                'user_id' => $advertiser->id,
                'provider_profile_id' => $profile->id,
                'title' => 'Test Advertiser — Paused Demo Listing',
            ],
            [
                'is_live' => false,
                'is_active' => true,
                'is_vip' => false,
            ]
        );

        // Inactive listing — demonstrates a disabled listing
        ProviderListing::updateOrCreate(
            [
                'user_id' => $advertiser->id,
                'provider_profile_id' => $profile->id,
                'title' => 'Test Advertiser — Inactive Demo Listing',
            ],
            [
                'is_live' => false,
                'is_active' => false,
                'is_vip' => false,
            ]
        );

        // Listing for the pending profile — shows pending-profile listing state
        ProviderListing::updateOrCreate(
            [
                'user_id' => $advertiser->id,
                'provider_profile_id' => $pendingProfile->id,
                'title' => 'Test Advertiser — Pending Review Listing',
            ],
            [
                'is_live' => false,
                'is_active' => false,
            ]
        );

        // -----------------------------------------------------------------------
        // 5. Profile images for main profile
        // -----------------------------------------------------------------------
        /** @var ProfileImage $primaryImage */
        $primaryImage = ProfileImage::withTrashed()->firstOrNew([
            'provider_profile_id' => $profile->id,
            'image_path' => self::PRIMARY_IMAGE_URL,
        ]);

        if ($primaryImage->trashed()) {
            $primaryImage->restore();
        }

        $primaryImage->fill([
            'user_id' => $advertiser->id,
            'thumbnail_path' => 'https://picsum.photos/seed/demo-advertiser-1-thumb/200/200',
            'is_primary' => true,
        ]);
        $primaryImage->save();

        // Additional demo images (2–4)
        for ($imgIndex = 2; $imgIndex <= 4; $imgIndex++) {
            $imageUrl = "https://picsum.photos/seed/demo-advertiser-{$imgIndex}/400/400";
            $thumbUrl = "https://picsum.photos/seed/demo-advertiser-{$imgIndex}-thumb/200/200";

            $extraImage = ProfileImage::withTrashed()->firstOrNew([
                'provider_profile_id' => $profile->id,
                'image_path' => $imageUrl,
            ]);

            if ($extraImage->trashed()) {
                $extraImage->restore();
            }

            $extraImage->fill([
                'user_id' => $advertiser->id,
                'thumbnail_path' => $thumbUrl,
                'is_primary' => false,
            ]);
            $extraImage->save();
        }

        // Ensure only the primary image is marked as primary
        ProfileImage::query()
            ->where('provider_profile_id', $profile->id)
            ->whereKeyNot($primaryImage->id)
            ->update(['is_primary' => false]);

        // Pending profile primary image
        $pendingPrimaryUrl = 'https://picsum.photos/seed/demo-advertiser-pending/400/400';
        $pendingImage = ProfileImage::withTrashed()->firstOrNew([
            'provider_profile_id' => $pendingProfile->id,
            'image_path' => $pendingPrimaryUrl,
        ]);
        if ($pendingImage->trashed()) {
            $pendingImage->restore();
        }
        $pendingImage->fill([
            'user_id' => $advertiser->id,
            'thumbnail_path' => 'https://picsum.photos/seed/demo-advertiser-pending-thumb/200/200',
            'is_primary' => true,
        ]);
        $pendingImage->save();

        // -----------------------------------------------------------------------
        // 6. Rates — demonstrates the rate/pricing section
        // -----------------------------------------------------------------------
        $rateGroup = RateGroup::updateOrCreate(
            ['provider_profile_id' => $profile->id, 'name' => 'Standard Rates'],
            ['user_id' => $advertiser->id]
        );

        foreach (self::RATE_DESCRIPTIONS as $rateIndex => $description) {
            Rate::updateOrCreate(
                ['provider_profile_id' => $profile->id, 'description' => $description],
                [
                    'user_id' => $advertiser->id,
                    'incall' => self::INCALL_PRICES[$rateIndex],
                    'outcall' => self::OUTCALL_PRICES[$rateIndex],
                    'extra' => $rateIndex === count(self::RATE_DESCRIPTIONS) - 1
                        ? '[DEMO] Includes dinner and overnight stay'
                        : null,
                    'group_id' => $rateGroup->id,
                ],
            );
        }

        // -----------------------------------------------------------------------
        // 7. Availability schedule — all 7 days
        // -----------------------------------------------------------------------
        foreach (self::DAYS as $dayIndex => $day) {
            $isWeekend = $dayIndex >= 5;
            Availability::updateOrCreate(
                ['provider_profile_id' => $profile->id, 'day' => $day],
                [
                    'user_id' => $advertiser->id,
                    'enabled' => true,
                    'from_time' => $isWeekend ? null : '09:00',
                    'to_time' => $isWeekend ? null : '22:00',
                    'till_late' => false,
                    'all_day' => $isWeekend,
                    'by_appointment' => false,
                ],
            );
        }

        // -----------------------------------------------------------------------
        // 8. Profile message
        // -----------------------------------------------------------------------
        ProfileMessage::updateOrCreate(
            ['provider_profile_id' => $profile->id],
            [
                'user_id' => $advertiser->id,
                'message' => '[DEMO] Hi! I am a demo advertiser profile used for payment processor compliance review. All content here is fictitious.',
            ]
        );

        // -----------------------------------------------------------------------
        // 9. Demo tours
        // -----------------------------------------------------------------------
        $demoTours = [
            ['city' => 'Melbourne', 'days_from_now' => 7, 'duration_days' => 3],
            ['city' => 'Brisbane', 'days_from_now' => 21, 'duration_days' => 4],
        ];

        foreach ($demoTours as $tourData) {
            $from = now()->addDays($tourData['days_from_now'])->setTime(10, 0);
            $to = (clone $from)->addDays($tourData['duration_days'])->setTime(18, 0);

            Tour::updateOrCreate(
                ['provider_profile_id' => $profile->id, 'city' => $tourData['city']],
                [
                    'user_id' => $advertiser->id,
                    'from' => $from,
                    'to' => $to,
                    'description' => "[DEMO] Visiting {$tourData['city']} — demo tour data for payment processor review.",
                    'enabled' => true,
                ],
            );
        }

        // -----------------------------------------------------------------------
        // 10. Online / availability status records (both set to offline for safety)
        // -----------------------------------------------------------------------
        OnlineUser::updateOrCreate(
            ['provider_profile_id' => $profile->id],
            [
                'user_id' => $advertiser->id,
                'status' => 'offline',
                'usage_date' => today(),
                'usage_count' => 0,
                'online_started_at' => null,
                'online_expires_at' => null,
            ]
        );

        AvailableNow::updateOrCreate(
            ['provider_profile_id' => $profile->id],
            [
                'user_id' => $advertiser->id,
                'status' => 'offline',
            ]
        );

        // -----------------------------------------------------------------------
        // 11. Purchase transactions — demonstrates the credit purchase flow
        //     All amounts use demo/fictitious payment references.
        // -----------------------------------------------------------------------
        /** @var PurchaseTransaction $tx1 */
        $tx1 = PurchaseTransaction::firstOrCreate(
            [
                'user_id' => $advertiser->id,
                'provider_profile_id' => $profile->id,
                'provider_checkout_id' => 'demo_tx_advertiser_001',
            ],
            [
                'provider' => 'stripe',
                'credits' => 100,
                'bonus_credits' => 0,
                'amount' => '49.00',
                'tax_amount' => '0.00',
                'currency' => 'AUD',
                'status' => 'paid',
                'invoice_name' => 'Test Advertiser',
                'paid_at' => now()->subDays(90),
                'created_at' => now()->subDays(90),
                'updated_at' => now()->subDays(90),
            ]
        );

        /** @var PurchaseTransaction $tx2 */
        $tx2 = PurchaseTransaction::firstOrCreate(
            [
                'user_id' => $advertiser->id,
                'provider_profile_id' => $profile->id,
                'provider_checkout_id' => 'demo_tx_advertiser_002',
            ],
            [
                'provider' => 'stripe',
                'credits' => 200,
                'bonus_credits' => 20,
                'amount' => '89.00',
                'tax_amount' => '0.00',
                'currency' => 'AUD',
                'status' => 'paid',
                'invoice_name' => 'Test Advertiser',
                'paid_at' => now()->subDays(45),
                'created_at' => now()->subDays(45),
                'updated_at' => now()->subDays(45),
            ]
        );

        // Pending transaction — demonstrates the pending checkout state
        PurchaseTransaction::firstOrCreate(
            [
                'user_id' => $advertiser->id,
                'provider_profile_id' => $profile->id,
                'provider_checkout_id' => 'demo_tx_advertiser_003_pending',
            ],
            [
                'provider' => 'stripe',
                'credits' => 50,
                'bonus_credits' => 0,
                'amount' => '29.00',
                'tax_amount' => '0.00',
                'currency' => 'AUD',
                'status' => 'pending',
                'invoice_name' => 'Test Advertiser',
                'paid_at' => null,
                'created_at' => now()->subDays(2),
                'updated_at' => now()->subDays(2),
            ]
        );

        // -----------------------------------------------------------------------
        // 12. Credit logs — demonstrates full credit transaction history
        //     Only inserted once so re-runs do not duplicate logs.
        //     reference_type/reference_id links logs to the main profile for the
        //     credit-history page (GetCreditHistory filters by these columns).
        // -----------------------------------------------------------------------
        $profileClass = ProviderProfile::class;

        if (! CreditLog::where('user_id', $advertiser->id)
            ->where('reference_type', $profileClass)
            ->where('reference_id', $profile->id)
            ->exists()) {

            $runningBalance = 0;

            $creditEntries = [
                // Initial purchase (tx1)
                [
                    'type' => 'credit',
                    'transaction_type' => 'purchase',
                    'amount' => 100,
                    'description' => '[DEMO] Credit purchase — 100 credits (AUD $49.00)',
                    'related_payment_id' => $tx1->id,
                    'days_ago' => 90,
                ],
                // Daily listing fees during first purchase period
                ['type' => 'debit', 'transaction_type' => 'daily_charge', 'amount' => -1, 'description' => '[DEMO] Daily listing fee', 'days_ago' => 85],
                ['type' => 'debit', 'transaction_type' => 'daily_charge', 'amount' => -1, 'description' => '[DEMO] Daily listing fee', 'days_ago' => 80],
                ['type' => 'debit', 'transaction_type' => 'daily_charge', 'amount' => -1, 'description' => '[DEMO] Daily listing fee', 'days_ago' => 75],
                ['type' => 'debit', 'transaction_type' => 'daily_charge', 'amount' => -1, 'description' => '[DEMO] Daily listing fee', 'days_ago' => 70],
                ['type' => 'debit', 'transaction_type' => 'daily_charge', 'amount' => -1, 'description' => '[DEMO] Daily listing fee', 'days_ago' => 65],
                ['type' => 'debit', 'transaction_type' => 'daily_charge', 'amount' => -1, 'description' => '[DEMO] Daily listing fee', 'days_ago' => 60],
                ['type' => 'debit', 'transaction_type' => 'daily_charge', 'amount' => -1, 'description' => '[DEMO] Daily listing fee', 'days_ago' => 55],
                ['type' => 'debit', 'transaction_type' => 'daily_charge', 'amount' => -1, 'description' => '[DEMO] Daily listing fee', 'days_ago' => 50],
                // Second purchase (tx2) — main credits
                [
                    'type' => 'credit',
                    'transaction_type' => 'purchase',
                    'amount' => 200,
                    'description' => '[DEMO] Credit purchase — 200 credits (AUD $89.00)',
                    'related_payment_id' => $tx2->id,
                    'days_ago' => 45,
                ],
                // Second purchase bonus credits
                [
                    'type' => 'credit',
                    'transaction_type' => 'bonus',
                    'amount' => 20,
                    'description' => '[DEMO] Bonus credits on purchase — 20 bonus credits',
                    'related_payment_id' => $tx2->id,
                    'days_ago' => 45,
                ],
                // Featured listing upgrade charge
                ['type' => 'debit', 'transaction_type' => 'featured_upgrade', 'amount' => -50, 'description' => '[DEMO] Featured listing upgrade — home featured placement', 'days_ago' => 40],
                // Further daily fees
                ['type' => 'debit', 'transaction_type' => 'daily_charge', 'amount' => -1, 'description' => '[DEMO] Daily listing fee', 'days_ago' => 35],
                ['type' => 'debit', 'transaction_type' => 'daily_charge', 'amount' => -1, 'description' => '[DEMO] Daily listing fee', 'days_ago' => 30],
                ['type' => 'debit', 'transaction_type' => 'daily_charge', 'amount' => -1, 'description' => '[DEMO] Daily listing fee', 'days_ago' => 25],
                ['type' => 'debit', 'transaction_type' => 'daily_charge', 'amount' => -1, 'description' => '[DEMO] Daily listing fee', 'days_ago' => 20],
                ['type' => 'debit', 'transaction_type' => 'daily_charge', 'amount' => -1, 'description' => '[DEMO] Daily listing fee', 'days_ago' => 15],
                // Local banner placement upgrade
                ['type' => 'debit', 'transaction_type' => 'featured_upgrade', 'amount' => -30, 'description' => '[DEMO] Local banner placement upgrade', 'days_ago' => 10],
                ['type' => 'debit', 'transaction_type' => 'daily_charge', 'amount' => -1, 'description' => '[DEMO] Daily listing fee', 'days_ago' => 5],
                ['type' => 'debit', 'transaction_type' => 'daily_charge', 'amount' => -1, 'description' => '[DEMO] Daily listing fee', 'days_ago' => 2],
            ];

            foreach ($creditEntries as $entry) {
                $runningBalance += $entry['amount'];
                $timestamp = now()->subDays($entry['days_ago']);

                CreditLog::create([
                    'user_id' => $advertiser->id,
                    'amount' => $entry['amount'],
                    'balance_after' => $runningBalance,
                    'type' => $entry['type'],
                    'transaction_type' => $entry['transaction_type'],
                    'status' => 'completed',
                    'description' => $entry['description'],
                    'related_payment_id' => $entry['related_payment_id'] ?? null,
                    'reference_type' => $profileClass,
                    'reference_id' => $profile->id,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }

            // -----------------------------------------------------------------------
            // 13. Wallet — reflects the final ledger balance for the main profile
            // -----------------------------------------------------------------------
            Wallet::updateOrCreate(
                ['provider_profile_id' => $profile->id],
                [
                    'user_id' => $advertiser->id,
                    'current_balance' => max(0, $runningBalance),
                ]
            );

            // Keep user and profile credits in sync with the wallet balance
            $advertiser->forceFill(['credits' => max(0, $runningBalance)])->save();
            $profile->forceFill(['credits' => max(0, $runningBalance)])->save();
        } else {
            // Ensure a wallet row exists even on re-runs where credit logs already exist
            Wallet::firstOrCreate(
                ['provider_profile_id' => $profile->id],
                [
                    'user_id' => $advertiser->id,
                    'current_balance' => max(0, $profile->credits ?? 0),
                ]
            );
        }

        $this->command?->info('Test advertiser account ready: ' . self::EMAIL);
        $this->command?->info('  Main profile    : ' . self::PROFILE_NAME . ' (approved, premium features)');
        $this->command?->info('  Pending profile : ' . self::PENDING_PROFILE_NAME . ' (pending review)');
        $this->command?->info('  Credit balance  : ' . ($advertiser->fresh()?->credits ?? 0) . ' credits');
    }

    /**
     * @return array<int>
     */
    private function resolveCategoryIds(string $parentSlug, int $limit): array
    {
        $parent = Category::query()->where('slug', $parentSlug)->first();

        if (! $parent) {
            return [];
        }

        return $parent->children()
            ->orderBy('sort_order')
            ->limit($limit)
            ->pluck('id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    private function resolveCategoryId(string $parentSlug): ?int
    {
        return $this->resolveCategoryIds($parentSlug, 1)[0] ?? null;
    }
}
