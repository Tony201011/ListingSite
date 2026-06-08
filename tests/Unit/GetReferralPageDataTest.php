<?php

namespace Tests\Unit;

use App\Actions\GetReferralPageData;
use App\Models\ProviderProfile;
use App\Models\Referral;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetReferralPageDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_backfills_a_missing_referral_code_for_existing_profile(): void
    {
        $user = User::factory()->create([
            'email' => 'provider@example.com',
            'role' => User::ROLE_PROVIDER,
        ]);

        $profile = ProviderProfile::query()->create([
            'user_id' => $user->id,
            'name' => 'Provider',
            'slug' => 'provider-'.$user->id,
            'account_user_referral_code' => null,
        ]);

        $result = app(GetReferralPageData::class)->execute($profile->fresh('user'));

        $profile->refresh();

        $this->assertNotNull($result['referralCode']);
        $this->assertSame($profile->account_user_referral_code, $result['referralCode']);
        $this->assertSame(url('/signup?ref='.$result['referralCode']), $result['referralLink']);
    }

    public function test_it_counts_only_active_referral_statuses(): void
    {
        $referrer = User::factory()->create(['role' => User::ROLE_PROVIDER]);
        $profile = ProviderProfile::query()->create([
            'user_id' => $referrer->id,
            'name' => 'Provider',
            'slug' => 'provider-'.$referrer->id,
            'account_user_referral_code' => 'ref1234567',
        ]);

        $referredUsers = User::factory()->count(4)->create();

        Referral::query()->create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $referredUsers[0]->id,
            'referral_code' => 'ref1234567',
            'status' => 'pending',
        ]);
        Referral::query()->create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $referredUsers[1]->id,
            'referral_code' => 'ref1234567',
            'status' => 'qualified',
        ]);
        Referral::query()->create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $referredUsers[2]->id,
            'referral_code' => 'ref1234567',
            'status' => 'rewarded',
        ]);
        Referral::query()->create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $referredUsers[3]->id,
            'referral_code' => 'ref1234567',
            'status' => 'cancelled',
        ]);

        $result = app(GetReferralPageData::class)->execute($profile);

        $this->assertSame('ref1234567', $result['referralCode']);
        $this->assertSame(3, $result['referralCount']);
    }
}
