<?php

namespace App\Actions\Subscription;

use App\Models\Invoice;
use App\Models\PurchaseTransaction;

class CreateInvoiceForPayment
{
    public function execute(PurchaseTransaction $payment): Invoice
    {
        $payment->loadMissing('creditPackage');

        return Invoice::query()->firstOrCreate(
            ['payment_id' => $payment->id],
            [
                'invoice_number' => $this->generateInvoiceNumber($payment),
                'user_id' => $payment->user_id,
                'package_name' => $payment->creditPackage?->name,
                'credits' => (int) $payment->total_credits,
                'amount' => $payment->amount,
                'currency' => $payment->currency,
                'tax_amount' => $payment->tax_amount ?? 0,
                'payment_provider' => $payment->provider,
                'payment_reference' => $payment->provider_transaction_id ?: $payment->provider_checkout_id,
                'purchased_at' => $payment->paid_at ?? $payment->created_at,
            ],
        );
    }

    private function generateInvoiceNumber(PurchaseTransaction $payment): string
    {
        return 'INV-'.($payment->paid_at ?? $payment->created_at)->format('Ymd').'-'.str_pad((string) $payment->id, 6, '0', STR_PAD_LEFT);
    }
}
