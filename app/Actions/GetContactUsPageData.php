<?php

namespace App\Actions;

use App\Models\ContactUsPage;
use App\Models\SiteSetting;
use Illuminate\Support\Facades\Schema;

class GetContactUsPageData
{
    public function execute(): array
    {
        $contactPage = null;
        if (Schema::hasTable('contact_us_pages')) {
            $contactPage = ContactUsPage::query()
                ->where('is_active', true)
                ->latest('updated_at')
                ->first();
        }

        $siteContactEmail = null;
        if (Schema::hasTable('site_settings')) {
            $siteContactEmail = SiteSetting::query()
                ->whereNotNull('contact_email')
                ->latest('id')
                ->value('contact_email');
        }

        return [
            'contactPage' => $contactPage,
            'contactEmail' => $contactPage?->support_email ?: ($siteContactEmail ?? 'support@hotescorts.com.au'),
        ];
    }
}
