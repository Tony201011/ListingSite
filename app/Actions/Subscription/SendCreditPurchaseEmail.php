<?php

namespace App\Actions\Subscription;

use App\Jobs\SendCreditPurchaseEmailJob;
use App\Models\PurchaseTransaction;

class SendCreditPurchaseEmail
{
    public function execute(PurchaseTransaction $transaction): void
    {
        $user = $transaction->user;

        if (! $user) {
            return;
        }

        SendCreditPurchaseEmailJob::dispatchSync(
            $user->email,
            $user->name,
            $transaction->credits,
            (float) $transaction->amount,
            $transaction->currency,
            $transaction->invoice_name,
        );
    }
}
