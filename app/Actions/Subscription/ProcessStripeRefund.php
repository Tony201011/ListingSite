<?php

namespace App\Actions\Subscription;

use App\Models\PurchaseTransaction;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\StripeClient;

class ProcessStripeRefund
{
    public function execute(PurchaseTransaction $transaction): void
    {
        if ($transaction->status !== 'paid') {
            throw new \RuntimeException('Only paid transactions can be refunded.');
        }

        $siteSetting = SiteSetting::first();

        if (! $siteSetting?->stripe_enabled || ! $siteSetting->stripe_secret_key) {
            throw new \RuntimeException('Stripe is not configured.');
        }

        if (! $transaction->stripe_payment_intent_id) {
            throw new \RuntimeException('No Stripe payment intent found for this transaction.');
        }

        $stripe = new StripeClient($siteSetting->stripe_secret_key);

        $refund = $stripe->refunds->create([
            'payment_intent' => $transaction->stripe_payment_intent_id,
        ]);

        Log::info('Stripe refund created', [
            'refund_id' => $refund->id,
            'transaction_id' => $transaction->id,
            'amount' => $transaction->amount,
        ]);

        DB::transaction(function () use ($transaction) {
            $locked = PurchaseTransaction::lockForUpdate()->find($transaction->id);

            if (! $locked || $locked->status !== 'paid') {
                throw new \RuntimeException('Transaction is no longer eligible for refund.');
            }

            $locked->update(['status' => 'refunded']);

            $user = $locked->user;
            if ($user) {
                if ($user->credits < $locked->credits) {
                    Log::warning('Refund credit deduction exceeds available credits', [
                        'transaction_id' => $locked->id,
                        'user_id' => $user->id,
                        'credits_to_deduct' => $locked->credits,
                        'credits_available' => $user->credits,
                    ]);
                    $user->update(['credits' => 0]);
                } else {
                    $user->decrement('credits', $locked->credits);
                }
            }

            Log::info('Transaction refunded and credits deducted', [
                'transaction_id' => $locked->id,
                'user_id' => $user?->id,
                'credits_deducted' => $locked->credits,
            ]);
        });
    }
}
