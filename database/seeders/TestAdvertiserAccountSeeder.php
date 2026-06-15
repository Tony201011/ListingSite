<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\ProfileImage;
use App\Models\ProviderListing;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Database\Seeder;

class TestAdvertiserAccountSeeder extends Seeder
{
    public const EMAIL = 'test-advertiser@example.com';

    public const PASSWORD = 'Advertiser@12345';

    private const PROFILE_NAME = 'Test Advertiser Profile';

    private const PROFILE_SLUG = 'test-advertiser-profile';

    private const PRIMARY_IMAGE_URL = 'https://images.example.com/demo/test-advertiser.jpg';

    public function run(): void
    {
        $freeListingDays = (int) (SiteSetting::getAdTierSettings()['free_listing_days'] ?? 21);

        /** @var User $advertiser */
        $advertiser = User::withTrashed()->firstOrCreate(
            ['email' => self::EMAIL],
            [
                'name' => 'Test Advertiser',
                'role' => User::ROLE_TEST_ADVERTISER,
                'password' => self::PASSWORD,
                'mobile' => null,
                'mobile_verified' => false,
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
            'mobile' => null,
            'mobile_verified' => false,
            'email_verified_at' => $advertiser->email_verified_at ?? now(),
            'account_status' => 'active',
            'is_blocked' => false,
        ]);

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
            'description' => 'Demo advertiser profile for testing layout and ad functionality only.',
            'introduction_line' => 'Demo advertiser account for QA and sandbox verification.',
            'profile_text' => 'Sample account for sandbox review. All visible details are placeholders.',
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
            'contact_method' => 'email-contact-form-only-phone-hidden',
            'phone_contact_preference' => null,
            'time_waster_shield' => 'no',
            'suburb' => 'Sydney CBD',
            'phone' => null,
            'is_blocked' => true,
            'profile_status' => 'approved',
            'free_listing_expires_at' => now()->addDays($freeListingDays),
        ]);
        $profile->save();

        ProviderListing::updateOrCreate(
            [
                'user_id' => $advertiser->id,
                'provider_profile_id' => $profile->id,
            ],
            [
                'title' => 'Test Advertiser Demo Listing',
                'is_live' => false,
                'is_active' => false,
            ]
        );

        /** @var ProfileImage $image */
        $image = ProfileImage::withTrashed()->firstOrNew([
            'provider_profile_id' => $profile->id,
            'image_path' => self::PRIMARY_IMAGE_URL,
        ]);

        if ($image->trashed()) {
            $image->restore();
        }

        $image->fill([
            'user_id' => $advertiser->id,
            'thumbnail_path' => self::PRIMARY_IMAGE_URL,
            'is_primary' => true,
        ]);
        $image->save();

        ProfileImage::query()
            ->where('provider_profile_id', $profile->id)
            ->whereKeyNot($image->id)
            ->update(['is_primary' => false]);

        $this->command?->info('Test advertiser account ready: ' . self::EMAIL);
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
