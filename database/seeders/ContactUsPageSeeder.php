<?php

namespace Database\Seeders;

use App\Models\ContactUsPage;
use Illuminate\Database\Seeder;

class ContactUsPageSeeder extends Seeder
{
    public function run(): void
    {
        $page = ContactUsPage::query()
            ->where('title', 'Contact/Support')
            ->latest('updated_at')
            ->first() ?? ContactUsPage::query()->latest('updated_at')->first() ?? new ContactUsPage;

        $page->fill([
            'title' => 'Contact/Support',
            'subtitle' => 'Need help, support, or want to lodge a complaint? Send us a message and our team will respond as soon as possible.',
            'support_heading' => 'Support & Complaints',
            'response_time' => 'Within 24 hours',
            'support_email' => 'support@hotescorts.com.au',
            'category_label' => 'contact-support',
            'enable_name_field' => true,
            'enable_email_field' => true,
            'enable_subject_field' => true,
            'enable_message_field' => true,
            'enable_map' => false,
            'map_latitude' => null,
            'map_longitude' => null,
            'is_active' => true,
        ]);

        $page->save();
    }
}
