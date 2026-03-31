<?php

namespace App\Actions;

use App\Jobs\SendBookingEnquiryEmailJob;
use App\Models\BookingEnquiry;

class SendBookingEnquiryEmail
{
    public function execute(BookingEnquiry $enquiry): void
    {
        SendBookingEnquiryEmailJob::dispatch($enquiry->id);
    }
}
