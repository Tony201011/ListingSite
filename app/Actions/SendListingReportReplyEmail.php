<?php

namespace App\Actions;

use App\Jobs\SendListingReportReplyEmailJob;
use App\Models\ListingReport;

class SendListingReportReplyEmail
{
    public function execute(ListingReport $report): void
    {
        SendListingReportReplyEmailJob::dispatch($report->id);
    }
}
