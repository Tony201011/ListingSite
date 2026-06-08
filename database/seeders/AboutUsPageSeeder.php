<?php

namespace Database\Seeders;

use App\Models\AboutUsPage;
use Illuminate\Database\Seeder;

class AboutUsPageSeeder extends Seeder
{
    public function run(): void
    {
        $page = AboutUsPage::query()
            ->where('title', 'About Us')
            ->latest('updated_at')
            ->first() ?? AboutUsPage::query()->latest('updated_at')->first() ?? new AboutUsPage;

        $page->fill([
            'title' => 'About Us',
            'content' => '<h2>Who we are</h2><p>We are an Australia-focused directory designed to help users browse listings quickly while giving advertisers easy tools to manage profile visibility, updates, and enquiries.</p><h3>What we provide</h3><ul><li>Public listing discovery and search</li><li>Advertiser registration and login tools</li><li>Credits-based profile management</li><li>Policy-first moderation and safety standards</li></ul><h3>Our commitment</h3><p>We continue to improve quality controls, support responsiveness, and account tools so both visitors and advertisers can use the platform with confidence.</p>',
            'is_active' => true,
        ]);

        $page->save();
    }
}
