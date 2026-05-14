<?php

namespace Tests\Unit;

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

        $result = (new PurchaseFeatured)->execute($user, $profile);

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

        (new PurchaseFeatured)->execute($user, $profile);

        $log = CreditLog::where('user_id', $user->id)->first();
        $this->assertNotNull($log);
        $this->assertSame(-5, $log->amount);
        $this->assertSame('used', $log->type);
    }

    public function test_purchase_fails_when_insufficient_credits(): void
    {
        $this->createSettings(creditCost: 5, durationDays: 7);
        [$user, $profile] = $this->createProvider(credits: 2);

        $result = (new PurchaseFeatured)->execute($user, $profile);

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
        [$user, $profile] = $this->createProvider(credits: 20);

        $existingExpiry = now()->addDays(3);
        $profile->is_featured = true;
        $profile->featured_expires_at = $existingExpiry;
        $profile->save();

        (new PurchaseFeatured)->execute($user, $profile);

        $profile->refresh();
        $this->assertTrue($profile->is_featured);
        // Expiry should be extended by 7 days from the existing expiry (3 + 7 = 10 days from now)
        $this->assertTrue($profile->featured_expires_at->isAfter($existingExpiry->addDays(6)));
    }

    public function test_get_featured_state_returns_correct_data(): void
    {
        $this->createSettings(creditCost: 5, durationDays: 7);
        [$user, $profile] = $this->createProvider(credits: 10);

        $profile->is_featured = true;
        $profile->featured_expires_at = now()->addDays(7);
        $profile->save();

        $state = (new \App\Actions\GetFeaturedState)->execute($profile);

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

        $state = (new \App\Actions\GetFeaturedState)->execute($profile);

        $this->assertFalse($state['isFeatured']);
        $this->assertNull($state['expiresAt']);

        $profile->refresh();
        $this->assertFalse((bool) $profile->is_featured);
        $this->assertNull($profile->featured_expires_at);
    }
}
