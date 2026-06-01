<?php

namespace App\Actions\Referral;

use App\Models\CreditLog;
use App\Models\PurchaseTransaction;
use App\Models\Referral;
use App\Models\SiteSetting;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class ProcessReferralRewardForFirstPayment
{
    public function execute(PurchaseTransaction $transaction): void
    {
        if ($transaction->status !== 'paid') {
            return;
        }

        $settings = SiteSetting::query()->first();
        if (! $settings || $settings->credit_destination !== 'wallet') {
            return;
        }

        if (($settings->reward_trigger ?? 'successful_payment') !== 'successful_payment') {
            return;
        }

        DB::transaction(function () use ($transaction, $settings): void {
            $referral = Referral::query()
                ->where('referred_user_id', $transaction->user_id)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->first();

            if (! $referral) {
                return;
            }

            $hasPriorPaid = PurchaseTransaction::query()
                ->where('user_id', $transaction->user_id)
                ->where('status', 'paid')
                ->where('id', '!=', $transaction->id)
                ->exists();

            if ($hasPriorPaid) {
                return;
            }

            $referral->update([
                'status' => 'qualified',
                'payment_id' => $transaction->id,
            ]);

            $creditedAmount = 0;
            $receiver = $settings->reward_receiver ?? 'referrer';

            if (in_array($receiver, ['referrer', 'both'], true)) {
                $amount = $this->calculateAmount(
                    (float) $transaction->amount,
                    (string) ($settings->reward_type ?? 'fixed'),
                    (float) ($settings->reward_value ?? 0)
                );

                if ($amount > 0) {
                    $this->creditWallet(
                        $referral->referrer_id,
                        $amount,
                        $referral->id,
                        "Referral reward credited for user #{$referral->referred_user_id} first successful payment"
                    );
                    $creditedAmount += $amount;
                }
            }

            if (in_array($receiver, ['referred', 'both'], true)) {
                $type = ($receiver === 'both' && (bool) $settings->referred_user_bonus_enabled)
                    ? (string) ($settings->referred_user_bonus_type ?? 'fixed')
                    : (string) ($settings->reward_type ?? 'fixed');
                $value = ($receiver === 'both' && (bool) $settings->referred_user_bonus_enabled)
                    ? (float) ($settings->referred_user_bonus_value ?? 0)
                    : (float) ($settings->reward_value ?? 0);

                $amount = $this->calculateAmount((float) $transaction->amount, $type, $value);
                if ($amount > 0) {
                    $this->creditWallet(
                        $referral->referred_user_id,
                        $amount,
                        $referral->id,
                        'Referral signup bonus credited after first successful payment'
                    );
                    $creditedAmount += $amount;
                }
            }

            $referral->update([
                'status' => 'rewarded',
                'reward_amount' => $creditedAmount,
                'rewarded_at' => now(),
            ]);
        });
    }

    private function calculateAmount(float $paymentAmount, string $type, float $value): int
    {
        $value = max($value, 0);

        if ($type === 'percentage') {
            return max((int) round(($paymentAmount * $value) / 100), 0);
        }

        return max((int) round($value), 0);
    }

    private function creditWallet(int $userId, int $amount, int $referralId, string $description): void
    {
        if ($amount <= 0) {
            return;
        }

        User::query()->whereKey($userId)->increment('credits', $amount);

        CreditLog::query()->create([
            'user_id' => $userId,
            'amount' => $amount,
            'type' => 'referral_reward',
            'transaction_type' => 'referral_reward',
            'status' => 'completed',
            'description' => $description,
            'reference_type' => Referral::class,
            'reference_id' => $referralId,
        ]);
    }
}
