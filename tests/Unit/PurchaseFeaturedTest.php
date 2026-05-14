<?php

namespace Tests\Unit;

use App\Actions\GetFeaturedState;
use App\Actions\PurchaseFeatured;
use App\Models\CreditLog;
use App\Models\ProviderProfile;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseFeaturedTest extends TestCase
{
    use RefreshDatabase;

    private function createSettings(int $creditCost = 5, int $durationDays = 7): SiteSetting
    {
        return SiteSetting::query()->create([
            'featured_credit_cost' => $creditCost,
            'featured_duration_days' => $durationDays,
        ]);
    }

    private function createProvider(int $credits = 10): array
    {
        $user = User::factory()->create([
            'role' => User::ROLE_PROVIDER,
            'credits' => $credits,
        ]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Provider',
            'slug' => 'provider-'.$user->id,
        ]);

        return [$user, $profile];
    }

    public function test_purchase_sets_is_featured_and_deducts_credits(): void
    {
        $this->createSettings(creditCost: 5, durationDays: 7);
        [$user, $profile] = $this->createProvider(credits: 10);

        $result = (new PurchaseFeatured)->execute($user, $profile, PurchaseFeatured::TIER_NORMAL, 1);

        $this->assertTrue($result->isSuccess());

        $profile->refresh();
        $this->assertTrue($profile->is_featured);
        $this->assertNotNull($profile->featured_expires_at);
        $this->assertTrue($profile->featured_expires_at->isFuture());

        $user->refresh();
        $this->assertSame(5, $user->credits);
    }

    public function test_purchase_creates_credit_log_entry(): void
    {
        $this->createSettings(creditCost: 5, durationDays: 7);
        [$user, $profile] = $this->createProvider(credits: 10);

        (new PurchaseFeatured)->execute($user, $profile, PurchaseFeatured::TIER_NORMAL, 1);

        $log = CreditLog::where('user_id', $user->id)->first();
        $this->assertNotNull($log);
        $this->assertSame(-5, $log->amount);
        $this->assertSame('used', $log->type);
    }

    public function test_purchase_fails_when_insufficient_credits(): void
    {
        $this->createSettings(creditCost: 5, durationDays: 7);
        [$user, $profile] = $this->createProvider(credits: 2);

        $result = (new PurchaseFeatured)->execute($user, $profile, PurchaseFeatured::TIER_NORMAL, 1);

        $this->assertFalse($result->isSuccess());
        $this->assertSame(422, $result->status());

        $profile->refresh();
        $this->assertFalse((bool) $profile->is_featured);

        $user->refresh();
        $this->assertSame(2, $user->credits);
    }

    public function test_purchase_extends_existing_featured_expiry(): void
    {
        $this->createSettings(creditCost: 5, durationDays: 7);
        [$user, $profile] = $this->createProvider(credits: 100);

        $existingExpiry = now()->addDays(3);
        $profile->is_featured = true;
        $profile->featured_expires_at = $existingExpiry->copy();
        $profile->save();

        (new PurchaseFeatured)->execute($user, $profile, PurchaseFeatured::TIER_NORMAL, 7);

        $profile->refresh();
        $this->assertTrue($profile->is_featured);
        // Expiry should be extended by 7 days from the existing expiry (3 + 7 = 10 days from now)
        // existingExpiry is ~now+3; expected new expiry is ~now+10; check it's after now+9
        $this->assertTrue($profile->featured_expires_at->isAfter($existingExpiry->copy()->addDays(6)));
    }

    public function test_purchase_charges_cost_per_day_times_days(): void
    {
        $this->createSettings(creditCost: 5, durationDays: 7);
        [$user, $profile] = $this->createProvider(credits: 100);

        (new PurchaseFeatured)->execute($user, $profile, PurchaseFeatured::TIER_NORMAL, 7);

        $user->refresh();
        // 5 credits/day × 7 days = 35 credits deducted
        $this->assertSame(65, $user->credits);

        $log = CreditLog::where('user_id', $user->id)->first();
        $this->assertSame(-35, $log->amount);
    }

    public function test_get_featured_state_returns_correct_data(): void
    {
        $this->createSettings(creditCost: 5, durationDays: 7);
        [$user, $profile] = $this->createProvider(credits: 10);

        $profile->is_featured = true;
        $profile->featured_expires_at = now()->addDays(7);
        $profile->save();

        $state = (new GetFeaturedState)->execute($profile);

        $this->assertTrue($state['isFeatured']);
        $this->assertNotNull($state['expiresAt']);
        $this->assertSame(5, $state['creditCost']);
        $this->assertSame(7, $state['durationDays']);
    }

    public function test_get_featured_state_expires_overdue_featured(): void
    {
        $this->createSettings(creditCost: 5, durationDays: 7);
        [$user, $profile] = $this->createProvider();

        $profile->is_featured = true;
        $profile->featured_expires_at = now()->subDay();
        $profile->save();

        $state = (new GetFeaturedState)->execute($profile);

        $this->assertFalse($state['isFeatured']);
        $this->assertNull($state['expiresAt']);

        $profile->refresh();
        $this->assertFalse((bool) $profile->is_featured);
        $this->assertNull($profile->featured_expires_at);
    }

    public function test_purchase_home_banner_tier_sets_correct_expiry_column(): void
    {
        SiteSetting::query()->create([
            'featured_duration_days' => 7,
            'home_banner_credit_cost' => 5,
        ]);
        [$user, $profile] = $this->createProvider(credits: 20);

        $result = (new PurchaseFeatured)->execute($user, $profile, PurchaseFeatured::TIER_HOME_BANNER, 1);

        $this->assertTrue($result->isSuccess());

        $profile->refresh();
        $this->assertNotNull($profile->home_banner_expires_at);
        $this->assertTrue($profile->home_banner_expires_at->isFuture());
        $this->assertFalse((bool) $profile->is_featured);
    }

    public function test_purchase_home_featured_tier_sets_correct_expiry_column(): void
    {
        SiteSetting::query()->create([
            'featured_duration_days' => 7,
            'home_featured_credit_cost' => 3,
        ]);
        [$user, $profile] = $this->createProvider(credits: 20);

        $result = (new PurchaseFeatured)->execute($user, $profile, PurchaseFeatured::TIER_HOME_FEATURED, 1);

        $this->assertTrue($result->isSuccess());

        $profile->refresh();
        $this->assertNotNull($profile->home_featured_expires_at);
        $this->assertTrue($profile->home_featured_expires_at->isFuture());
    }

    public function test_purchase_local_banner_tier_sets_correct_expiry_column(): void
    {
        SiteSetting::query()->create([
            'featured_duration_days' => 7,
            'local_banner_credit_cost' => 2,
        ]);
        [$user, $profile] = $this->createProvider(credits: 20);

        $result = (new PurchaseFeatured)->execute($user, $profile, PurchaseFeatured::TIER_LOCAL_BANNER, 1);

        $this->assertTrue($result->isSuccess());

        $profile->refresh();
        $this->assertNotNull($profile->local_banner_expires_at);
        $this->assertTrue($profile->local_banner_expires_at->isFuture());
    }

    public function test_get_featured_state_includes_all_tier_expiries(): void
    {
        $this->createSettings(creditCost: 1, durationDays: 7);
        [$user, $profile] = $this->createProvider(credits: 10);

        $profile->home_featured_expires_at = now()->addDays(3);
        $profile->local_banner_expires_at = now()->addDays(5);
        $profile->home_banner_expires_at = now()->addDays(7);
        $profile->save();

        $state = (new GetFeaturedState)->execute($profile);

        $this->assertNotNull($state['homeFeaturedExpiresAt']);
        $this->assertNotNull($state['localBannerExpiresAt']);
        $this->assertNotNull($state['homeBannerExpiresAt']);
    }
}
