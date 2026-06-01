<?php

namespace App\Actions\Referral;

use App\Models\CreditLog;
use App\Models\PurchaseTransaction;
use App\Models\Referral;
use App\Models\User;

class ReverseReferralRewardForRefund
{
    public function execute(PurchaseTransaction $transaction): void
    {
        $referral = Referral::query()
            ->where('payment_id', $transaction->id)
            ->whereIn('status', ['pending', 'qualified', 'rewarded'])
            ->lockForUpdate()
            ->first();

        if (! $referral) {
            return;
        }

        if ($referral->status === 'rewarded') {
            $creditLogs = CreditLog::query()
                ->where('transaction_type', 'referral_reward')
                ->where('reference_type', Referral::class)
                ->where('reference_id', $referral->id)
                ->where('status', 'completed')
                ->get();

            foreach ($creditLogs as $creditLog) {
                $deductionAmount = abs((int) $creditLog->amount);
                if ($deductionAmount <= 0) {
                    continue;
                }

                User::query()->whereKey($creditLog->user_id)->decrement('credits', $deductionAmount);

                CreditLog::query()->create([
                    'user_id' => $creditLog->user_id,
                    'amount' => -$deductionAmount,
                    'type' => 'referral_reward',
                    'transaction_type' => 'referral_reward',
                    'status' => 'reversed',
                    'description' => "Referral reward reversed for refunded/cancelled payment #{$transaction->id}",
                    'reference_type' => Referral::class,
                    'reference_id' => $referral->id,
                ]);

                $creditLog->update(['status' => 'reversed']);
            }
        }

        $referral->update(['status' => 'cancelled']);
    }
}
