<?php

namespace Database\Seeders;

use App\Models\ReportAListingPage;
use Illuminate\Database\Seeder;

class ReportAListingPageSeeder extends Seeder
{
    public function run(): void
    {
        ReportAListingPage::updateOrCreate(
            [
                'title' => 'Report a Listing',
            ],
            [
                'content' => '<h2>How to Report a Listing</h2><p>Use this form to report listings that may include misleading details, stolen photos, impersonation, abuse, or illegal activity. Share clear evidence so our team can review quickly.</p><h3>Example Incident</h3><p>Example: you noticed a suspicious listing day before yesterday and want us to review it. Add the listing URL, advertiser name, and a short explanation of what happened.</p><h3>What to Include</h3><p>Provide as much detail as possible: listing ID, contact details shown on the ad, location, and supporting screenshots or PDF files.</p><h3>Review Process</h3><p>After submission, reports are triaged by priority and reviewed by moderation. We may contact you for more information if needed.</p><h3>Urgent Safety Notice</h3><p>If someone is in immediate danger, call emergency services first. This report form is not monitored as an emergency channel.</p>',
                'is_active' => true,
            ],
        );
    }
}
