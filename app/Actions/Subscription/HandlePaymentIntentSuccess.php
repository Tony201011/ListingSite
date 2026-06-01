<?php

namespace App\Actions\Subscription;

use App\Actions\Referral\ProcessReferralRewardForFirstPayment;
use App\Models\CreditLog;
use App\Models\ProviderProfile;
use App\Models\PurchaseTransaction;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

class HandlePaymentIntentSuccess
{
    public function __construct(
        private SendCreditPurchaseEmail $sendCreditPurchaseEmail,
        private ProcessReferralRewardForFirstPayment $processReferralRewardForFirstPayment,
    ) {}

    /**
     * @return array{status: string, credits?: int, error?: string}
     */
    public function execute(string $paymentIntentId): array
    {
        $transaction = PurchaseTransaction::where('stripe_payment_intent_id', $paymentIntentId)
            ->where('user_id', Auth::id())
            ->first();

        if (! $transaction) {
            return ['status' => 'not_found'];
        }

        if ($transaction->status === 'paid') {
            return ['status' => 'paid', 'credits' => $transaction->credits];
        }

        $siteSetting = SiteSetting::first();

        if (! $siteSetting?->stripe_enabled || ! $siteSetting->stripe_secret_key) {
            return ['status' => 'not_configured'];
        }

        try {
            $stripe = new StripeClient($siteSetting->stripe_secret_key);
            $paymentIntent = $stripe->paymentIntents->retrieve($paymentIntentId, [
                'expand' => ['latest_charge'],
            ]);

            if ($paymentIntent->status !== 'succeeded') {
                return ['status' => 'unpaid'];
            }

            $receiptUrl = $paymentIntent->latest_charge?->receipt_url ?? null;

            DB::transaction(function () use ($transaction, $paymentIntent, $paymentIntentId, $receiptUrl) {
                $locked = PurchaseTransaction::lockForUpdate()->find($transaction->id);
                if ($locked && $locked->status !== 'paid') {
                    $locked->update([
                        'status' => 'paid',
                        'stripe_payment_intent_id' => $paymentIntentId,
                        'receipt_url' => $receiptUrl,
                        'paid_at' => now(),
                        'provider_profile_id' => $locked->provider_profile_id ?: (int) ($paymentIntent->metadata->provider_profile_id ?? 0) ?: null,
                    ]);
                    $profile = $locked->providerProfile;
                    if ($profile) {
                        $profile->increment('credits', $locked->credits);
                        CreditLog::create([
                            'user_id' => $locked->user_id,
                            'amount' => $locked->credits,
                            'type' => 'purchase_credit',
                            'description' => "Purchased {$locked->credits} credits",
                            'reference_type' => ProviderProfile::class,
                            'reference_id' => $profile->id,
                        ]);
                    }

                    $this->processReferralRewardForFirstPayment->execute($locked);
                }
            });

            $transaction->refresh();

            $this->sendCreditPurchaseEmail->execute($transaction);

            return ['status' => 'paid', 'credits' => $transaction->credits];
        } catch (\Exception $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }
}
