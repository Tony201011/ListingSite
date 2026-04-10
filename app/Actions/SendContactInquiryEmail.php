<?php

namespace App\Actions;

use App\Jobs\SendContactInquiryEmailJob;
use App\Models\ContactInquiry;

class SendContactInquiryEmail
{
    public function execute(ContactInquiry $inquiry): void
    {
        SendContactInquiryEmailJob::dispatch($inquiry->id);
    }
}
