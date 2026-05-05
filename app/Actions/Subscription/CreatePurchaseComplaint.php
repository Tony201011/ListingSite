<?php

namespace App\Actions\Subscription;

use App\Actions\SendPurchaseComplaintEmail;
use App\Models\PurchaseComplaint;
use App\Models\PurchaseTransaction;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreatePurchaseComplaint
{
    public function __construct(
        private SendPurchaseComplaintEmail $sendPurchaseComplaintEmail
    ) {}

    public function execute(PurchaseTransaction $transaction, array $validated): PurchaseComplaint
    {
        $complaint = DB::transaction(function () use ($transaction, $validated): PurchaseComplaint {
            return PurchaseComplaint::create([
                'user_id' => Auth::id(),
                'purchase_transaction_id' => $transaction->id,
                'subject' => $validated['subject'],
                'message' => $validated['message'],
                'status' => 'pending',
                'is_read' => false,
            ]);
        });

        try {
            $this->sendPurchaseComplaintEmail->execute($complaint);
        } catch (Throwable $e) {
            Log::warning('Purchase complaint email dispatch failed after complaint was saved.', [
                'purchase_complaint_id' => $complaint->id,
                'error' => $e->getMessage(),
            ]);
        }

        return $complaint;
    }
}
