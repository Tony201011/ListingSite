<?php

namespace App\Actions;

use App\Models\ContactUsPage;
use App\Models\SiteSetting;

class GetContactUsPageData
{
    public function execute(): array
    {
        $contactPage = ContactUsPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        $siteContactEmail = SiteSetting::query()
            ->whereNotNull('contact_email')
            ->latest('id')
            ->value('contact_email');

        return [
            'contactPage' => $contactPage,
            'contactEmail' => $contactPage?->support_email ?: ($siteContactEmail ?? 'support@hotescorts.com.au'),
        ];
    }
}
