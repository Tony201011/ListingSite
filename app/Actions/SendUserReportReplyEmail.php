<?php

namespace App\Actions;

use App\Jobs\SendUserReportReplyEmailJob;
use App\Models\UserReport;

class SendUserReportReplyEmail
{
    public function execute(UserReport $report): void
    {
        SendUserReportReplyEmailJob::dispatch($report->id);
    }
}
