<?php

namespace App\Actions\Auth;

use App\Models\GoogleRecaptchaSetting;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

class BuildAuthPageData
{
    public function execute(): array
    {
        $recaptchaSetting = GoogleRecaptchaSetting::query()
            ->where('is_active', 1)
            ->first();

        $shouldUseRecaptcha = $this->shouldUseRecaptcha($recaptchaSetting);

        return [
            'recaptchaSetting' => $recaptchaSetting,
            'shouldUseRecaptcha' => $shouldUseRecaptcha,
        ];
    }

    private function shouldUseRecaptcha(?GoogleRecaptchaSetting $recaptchaSetting): bool
    {
        if (! $this->isCaptchaEnabledInSiteSettings()) {
            return false;
        }

        return filled($recaptchaSetting?->site_key)
            && filled($recaptchaSetting?->secret_key);
    }

    private function isCaptchaEnabledInSiteSettings(): bool
    {
        if (! Schema::hasTable('site_settings')) {
            return true;
        }

        $siteSetting = SiteSetting::query()->latest('updated_at')->first();

        return $siteSetting?->captcha_enabled ?? true;
    }
}
