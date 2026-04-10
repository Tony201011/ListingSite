<?php

namespace App\Filament\Concerns;

use App\Models\EmailLog;
use Illuminate\Support\Carbon;

trait ChecksEmailSendingOutcome
{
    protected function hasRecentEmailFailure(string $email, Carbon $since): bool
    {
        return EmailLog::where('recipient', $email)
            ->whereIn('type', ['account_created', 'verify_email'])
            ->where('status', 'failed')
            ->where('sent_at', '>=', $since)
            ->exists();
    }
}
