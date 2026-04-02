<?php

namespace App\Actions\Auth;

use App\Jobs\SendProviderAccountEmailsJob;
use App\Models\SmtpSetting;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SendProviderAccountEmails
{
    public function execute(User $user): void
    {
        $activeMailSetting = SmtpSetting::query()
            ->where('is_enabled', true)
            ->latest('updated_at')
            ->first();

        if (! $activeMailSetting) {
            $activeMailSetting = SmtpSetting::query()
                ->latest('updated_at')
                ->first();
        }

        if (! $activeMailSetting) {
            Log::error('Signup emails failed: no active mail setting found.', [
                'user_id' => $user->id,
                'email' => $user->email,
            ]);

            return;
        }

        SendProviderAccountEmailsJob::dispatchSync($user->id, $activeMailSetting->id);
    }
}
