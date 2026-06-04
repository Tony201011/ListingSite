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

    private function createSettings(int $creditCost = 5, int $durationDays = 1): SiteSetting
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
        ]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Provider',
            'slug' => 'provider-'.$user->id,
            'credits' => $credits,
        ]);

        return [$user, $profile];
    }

    public function test_purchase_sets_is_featured_and_deducts_credits(): void
    {
        $this->createSettings(creditCost: 5, durationDays: 1);
        [$user, $profile] = $this->createProvider(credits: 10);

        $result = app(PurchaseFeatured::class)->execute($user, $profile);

        $this->assertTrue($result->isSuccess());

        $profile->refresh();
        $this->assertTrue($profile->is_featured);
        $this->assertNotNull($profile->featured_expires_at);
        $this->assertTrue($profile->featured_expires_at->isFuture());

        $profile->refresh();
        $this->assertSame(5, $profile->credits);
    }

    public function test_purchase_creates_credit_log_entry(): void
    {
        $this->createSettings(creditCost: 5, durationDays: 1);
        [$user, $profile] = $this->createProvider(credits: 10);

        app(PurchaseFeatured::class)->execute($user, $profile);

        $log = CreditLog::where('user_id', $user->id)->first();
        $this->assertNotNull($log);
        $this->assertSame(-5, $log->amount);
        $this->assertSame('used', $log->type);
    }

    public function test_purchase_fails_when_insufficient_credits(): void
    {
        $this->createSettings(creditCost: 5, durationDays: 1);
        [$user, $profile] = $this->createProvider(credits: 2);

        $result = app(PurchaseFeatured::class)->execute($user, $profile);

        $this->assertFalse($result->isSuccess());
        $this->assertSame(422, $result->status());

        $profile->refresh();
        $this->assertFalse((bool) $profile->is_featured);

        $profile->refresh();
        $this->assertSame(2, $profile->credits);
    }

    public function test_purchase_extends_existing_featured_expiry(): void
    {
        $this->createSettings(creditCost: 5, durationDays: 1);
        [$user, $profile] = $this->createProvider(credits: 20);

        $existingExpiry = now()->addDays(3);
        $profile->is_featured = true;
        $profile->featured_expires_at = $existingExpiry;
        $profile->save();

        app(PurchaseFeatured::class)->execute($user, $profile);

        $profile->refresh();
        $this->assertTrue($profile->is_featured);
        // Expiry should be extended by 1 day from the existing expiry.
        $this->assertTrue($profile->featured_expires_at->isAfter($existingExpiry->copy()->addHours(12)));
    }

    public function test_get_featured_state_returns_correct_data(): void
    {
        $this->createSettings(creditCost: 5, durationDays: 1);
        [$user, $profile] = $this->createProvider(credits: 10);

        $profile->is_featured = true;
        $profile->featured_expires_at = now()->addDays(7);
        $profile->save();

        $state = (new GetFeaturedState)->execute($profile);

        $this->assertTrue($state['isFeatured']);
        $this->assertNotNull($state['expiresAt']);
        $this->assertSame(5, $state['creditCost']);
        $this->assertSame(1, $state['durationDays']);
    }

    public function test_get_featured_state_expires_overdue_featured(): void
    {
        $this->createSettings(creditCost: 5, durationDays: 1);
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
            'featured_duration_days' => 1,
            'home_banner_credit_cost' => 5,
        ]);
        [$user, $profile] = $this->createProvider(credits: 20);

        $result = app(PurchaseFeatured::class)->execute($user, $profile, PurchaseFeatured::TIER_HOME_BANNER);

        $this->assertTrue($result->isSuccess());

        $profile->refresh();
        $this->assertNotNull($profile->home_banner_expires_at);
        $this->assertTrue($profile->home_banner_expires_at->isFuture());
        $this->assertFalse((bool) $profile->is_featured);
    }

    public function test_purchase_home_featured_tier_sets_correct_expiry_column(): void
    {
        SiteSetting::query()->create([
            'featured_duration_days' => 1,
            'home_featured_credit_cost' => 3,
        ]);
        [$user, $profile] = $this->createProvider(credits: 20);

        $result = app(PurchaseFeatured::class)->execute($user, $profile, PurchaseFeatured::TIER_HOME_FEATURED);

        $this->assertTrue($result->isSuccess());

        $profile->refresh();
        $this->assertNotNull($profile->home_featured_expires_at);
        $this->assertTrue($profile->home_featured_expires_at->isFuture());
    }

    public function test_purchase_local_banner_tier_sets_correct_expiry_column(): void
    {
        SiteSetting::query()->create([
            'featured_duration_days' => 1,
            'local_banner_credit_cost' => 2,
        ]);
        [$user, $profile] = $this->createProvider(credits: 20);

        $result = app(PurchaseFeatured::class)->execute($user, $profile, PurchaseFeatured::TIER_LOCAL_BANNER);

        $this->assertTrue($result->isSuccess());

        $profile->refresh();
        $this->assertNotNull($profile->local_banner_expires_at);
        $this->assertTrue($profile->local_banner_expires_at->isFuture());
    }

    public function test_get_featured_state_includes_all_tier_expiries(): void
    {
        $this->createSettings(creditCost: 1, durationDays: 1);
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
