<?php

namespace App\Actions;

use App\Jobs\SendContactInquiryReplyEmailJob;
use App\Models\ContactInquiry;
use App\Models\ContactInquiryReply;

class SendContactInquiryReplyEmail
{
    public function execute(ContactInquiry $inquiry, ContactInquiryReply $reply): void
    {
        SendContactInquiryReplyEmailJob::dispatch($inquiry->id, $reply->id);
    }
}
