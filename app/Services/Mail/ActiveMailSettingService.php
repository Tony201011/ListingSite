<?php

namespace App\Services\Mail;

use App\Models\SmtpSetting;

class ActiveMailSettingService
{
    public function getActiveOrLatest(): ?SmtpSetting
    {
        $activeMailSetting = SmtpSetting::query()
            ->where('is_enabled', true)
            ->latest('updated_at')
            ->first();

        if ($activeMailSetting) {
            return $activeMailSetting;
        }

        return SmtpSetting::query()
            ->latest('updated_at')
            ->first();
    }
}
