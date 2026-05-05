<?php

namespace App\Actions;

use App\Jobs\SendPurchaseComplaintReplyEmailJob;
use App\Models\PurchaseComplaint;

class SendPurchaseComplaintReplyEmail
{
    public function execute(PurchaseComplaint $complaint): void
    {
        SendPurchaseComplaintReplyEmailJob::dispatch($complaint->id);
    }
}
