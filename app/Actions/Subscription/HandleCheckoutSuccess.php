<?php

namespace App\Actions\Subscription;

use App\Models\CreditLog;
use App\Models\PurchaseTransaction;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

class HandleCheckoutSuccess
{
    public function __construct(
        private SendCreditPurchaseEmail $sendCreditPurchaseEmail,
    ) {}

    /**
     * @return array{status: string, credits?: int, error?: string}
     */
    public function execute(string $sessionId): array
    {
        $transaction = PurchaseTransaction::where('stripe_session_id', $sessionId)
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
            $session = $stripe->checkout->sessions->retrieve($sessionId, [
                'expand' => ['payment_intent.latest_charge'],
            ]);

            if ($session->payment_status !== 'paid') {
                return ['status' => 'unpaid'];
            }

            if ($transaction->status !== 'paid') {
                $receiptUrl = $session->payment_intent?->latest_charge?->receipt_url ?? null;

                DB::transaction(function () use ($transaction, $session, $receiptUrl) {
                    $locked = PurchaseTransaction::lockForUpdate()->find($transaction->id);
                    if ($locked && $locked->status !== 'paid') {
                        $locked->update([
                            'status' => 'paid',
                            'stripe_payment_intent_id' => is_string($session->payment_intent)
                                ? $session->payment_intent
                                : $session->payment_intent?->id,
                            'receipt_url' => $receiptUrl,
                            'paid_at' => now(),
                        ]);
                        $locked->user?->increment('credits', $locked->credits);
                        if ($locked->user) {
                            CreditLog::create([
                                'user_id' => $locked->user->id,
                                'amount' => $locked->credits,
                                'type' => 'purchase_credit',
                                'description' => "Purchased {$locked->credits} credits",
                                'reference_type' => PurchaseTransaction::class,
                                'reference_id' => $locked->id,
                            ]);
                        }
                    }
                });

                $transaction->refresh();
            }

            $this->sendCreditPurchaseEmail->execute($transaction);

            return ['status' => 'paid', 'credits' => $transaction->credits];
        } catch (\Exception $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }
}
