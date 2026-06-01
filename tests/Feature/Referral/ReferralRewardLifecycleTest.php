<?php

namespace Tests\Feature\Referral;

use App\Actions\Referral\ProcessReferralRewardForFirstPayment;
use App\Actions\Referral\ReverseReferralRewardForRefund;
use App\Models\CreditLog;
use App\Models\PurchaseTransaction;
use App\Models\Referral;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReferralRewardLifecycleTest extends TestCase
{
    use RefreshDatabase;

    public function test_first_successful_payment_credits_referrer_wallet_and_marks_referral_rewarded(): void
    {
        $referrer = User::factory()->create(['credits' => 0]);
        $referred = User::factory()->create(['credits' => 0]);

        SiteSetting::query()->create([
            'reward_receiver' => 'referrer',
            'reward_trigger' => 'successful_payment',
            'reward_type' => 'fixed',
            'reward_value' => 20,
            'referred_user_bonus_enabled' => false,
            'credit_destination' => 'wallet',
        ]);

        $referral = Referral::query()->create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $referred->id,
            'referral_code' => 'REFCODE1',
            'status' => 'pending',
        ]);

        $transaction = PurchaseTransaction::query()->create([
            'user_id' => $referred->id,
            'credits' => 100,
            'amount' => 50,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        app(ProcessReferralRewardForFirstPayment::class)->execute($transaction);

        $referrer->refresh();
        $referral->refresh();

        $this->assertSame(20, (int) $referrer->credits);
        $this->assertSame('rewarded', $referral->status);
        $this->assertSame($transaction->id, $referral->payment_id);
        $this->assertDatabaseHas((new CreditLog)->getTable(), [
            'user_id' => $referrer->id,
            'amount' => 20,
            'type' => 'referral_reward',
            'transaction_type' => 'referral_reward',
            'status' => 'completed',
            'reference_type' => Referral::class,
            'reference_id' => $referral->id,
        ]);
    }

    public function test_referral_reward_is_only_applied_for_first_successful_payment(): void
    {
        $referrer = User::factory()->create(['credits' => 0]);
        $referred = User::factory()->create(['credits' => 0]);

        SiteSetting::query()->create([
            'reward_receiver' => 'referrer',
            'reward_trigger' => 'successful_payment',
            'reward_type' => 'fixed',
            'reward_value' => 10,
            'credit_destination' => 'wallet',
        ]);

        Referral::query()->create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $referred->id,
            'referral_code' => 'REFCODE2',
            'status' => 'pending',
        ]);

        $firstTransaction = PurchaseTransaction::query()->create([
            'user_id' => $referred->id,
            'credits' => 10,
            'amount' => 10,
            'status' => 'paid',
            'paid_at' => now()->subMinute(),
        ]);

        app(ProcessReferralRewardForFirstPayment::class)->execute($firstTransaction);

        $secondTransaction = PurchaseTransaction::query()->create([
            'user_id' => $referred->id,
            'credits' => 20,
            'amount' => 20,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        app(ProcessReferralRewardForFirstPayment::class)->execute($secondTransaction);

        $referrer->refresh();

        $this->assertSame(10, (int) $referrer->credits);
        $this->assertSame(1, CreditLog::query()->where('transaction_type', 'referral_reward')->where('amount', '>', 0)->count());
    }

    public function test_referral_reward_is_reversed_when_payment_is_refunded(): void
    {
        $referrer = User::factory()->create(['credits' => 0]);
        $referred = User::factory()->create(['credits' => 0]);

        SiteSetting::query()->create([
            'reward_receiver' => 'referrer',
            'reward_trigger' => 'successful_payment',
            'reward_type' => 'fixed',
            'reward_value' => 15,
            'credit_destination' => 'wallet',
        ]);

        $referral = Referral::query()->create([
            'referrer_id' => $referrer->id,
            'referred_user_id' => $referred->id,
            'referral_code' => 'REFCODE3',
            'status' => 'pending',
        ]);

        $transaction = PurchaseTransaction::query()->create([
            'user_id' => $referred->id,
            'credits' => 50,
            'amount' => 25,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        app(ProcessReferralRewardForFirstPayment::class)->execute($transaction);
        app(ReverseReferralRewardForRefund::class)->execute($transaction);

        $referrer->refresh();
        $referral->refresh();

        $this->assertSame(0, (int) $referrer->credits);
        $this->assertSame('cancelled', $referral->status);
        $this->assertDatabaseHas((new CreditLog)->getTable(), [
            'user_id' => $referrer->id,
            'amount' => -15,
            'transaction_type' => 'referral_reward',
            'status' => 'reversed',
            'reference_type' => Referral::class,
            'reference_id' => $referral->id,
        ]);
    }
}
