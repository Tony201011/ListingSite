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
            CategorySeeder::class,
            DummyProviderSeeder::class,
            DummyProviderListingSeeder::class,
            TermConditionSeeder::class,
            PrivacyPolicySeeder::class,
            RefundPolicySeeder::class,
            FaqSeeder::class,
            AntiSpamPolicySeeder::class,
            SocialLoginSettingSeeder::class,
            SmtpSettingSeeder::class,
            S3BucketSettingSeeder::class,
            GenderTabSeeder::class,
            MetaKeywordSeeder::class,
            MetaDescriptionSeeder::class,
        ]);
    }
}
