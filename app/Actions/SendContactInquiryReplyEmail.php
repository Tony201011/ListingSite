<?php

namespace App\Actions;

use App\Jobs\SendContactInquiryReplyEmailJob;
use App\Models\ContactInquiry;

class SendContactInquiryReplyEmail
{
    public function execute(ContactInquiry $inquiry): void
    {
        SendContactInquiryReplyEmailJob::dispatch($inquiry->id);
    }
}
