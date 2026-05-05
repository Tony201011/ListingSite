<?php

namespace App\Actions;

use App\Jobs\SendPurchaseComplaintEmailJob;
use App\Models\PurchaseComplaint;

class SendPurchaseComplaintEmail
{
    public function execute(PurchaseComplaint $complaint): void
    {
        SendPurchaseComplaintEmailJob::dispatch($complaint->id);
    }
}
