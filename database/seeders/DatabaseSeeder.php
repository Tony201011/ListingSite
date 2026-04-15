<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $admin = User::where('email', 'admin@example.com')->first();
        if ($admin) {
            $admin->update([
                'password' => bcrypt('admin123'), // Change password as needed
            ]);
        } else {
            User::factory()->create([
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'role' => 'admin',
                'password' => bcrypt('admin123'), // Change password as needed
            ]);
        }

        $this->call([
            LocationImportSeeder::class,
            LocationSeeder::class,
            CategorySeeder::class,
            TourCitySeeder::class,
            PostcodeSeeder::class,
            SiteSettingSeeder::class,
            DummyProviderProfileSeeder::class,
            DummyProviderListingSeeder::class,
            VerificationExampleImageSeeder::class,
            BlogPostSeeder::class,
            TermConditionSeeder::class,
            PrivacyPolicySeeder::class,
            RefundPolicySeeder::class,
            AntiSpamPolicySeeder::class,
            NaughtyCornerPageSeeder::class,
            PricingPageSeeder::class,
            PricingPackageSeeder::class,
            HelpPageSeeder::class,
            GlobalBannerSeeder::class,
            FooterTextSeeder::class,
            HeaderWidgetSeeder::class,
            FaqSeeder::class,
            SmtpSettingSeeder::class,
            S3BucketSettingSeeder::class,
            GoogleRecaptchaSettingSeeder::class,
            //  CookieSettingSeeder::class,
            MetaKeywordSeeder::class,
            MetaDescriptionSeeder::class,
        ]);
    }
}
