<?php

namespace App\Actions\Subscription;

use App\Models\PurchaseTransaction;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

class HandleCheckoutSuccess
{
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

        // Webhook may not have arrived yet — verify directly with Stripe as fallback
        $siteSetting = SiteSetting::first();
        if (! $siteSetting?->stripe_enabled || ! $siteSetting->stripe_secret_key) {
            return ['status' => 'not_configured'];
        }

        try {
            $stripe = new StripeClient($siteSetting->stripe_secret_key);
            $session = $stripe->checkout->sessions->retrieve($sessionId);

            if ($session->payment_status !== 'paid') {
                return ['status' => 'unpaid'];
            }

            if ($transaction->status !== 'paid') {
                DB::transaction(function () use ($transaction, $session) {
                    $locked = PurchaseTransaction::lockForUpdate()->find($transaction->id);
                    if ($locked && $locked->status !== 'paid') {
                        $locked->update([
                            'status' => 'paid',
                            'stripe_payment_intent_id' => $session->payment_intent,
                            'paid_at' => now(),
                        ]);
                        $locked->user?->increment('credits', $locked->credits);
                    }
                });

                $transaction->refresh();
            }

            return ['status' => 'paid', 'credits' => $transaction->credits];
        } catch (\Exception $e) {
            return ['status' => 'error', 'error' => $e->getMessage()];
        }
    }
}
