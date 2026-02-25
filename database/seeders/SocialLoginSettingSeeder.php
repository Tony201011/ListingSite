<?php

namespace Database\Seeders;

use App\Models\SocialLoginSetting;
use Illuminate\Database\Seeder;

class SocialLoginSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $providers = [
            SocialLoginSetting::PROVIDER_GOOGLE,
            SocialLoginSetting::PROVIDER_FACEBOOK,
            SocialLoginSetting::PROVIDER_TWITTER,
        ];

        foreach ($providers as $provider) {
            SocialLoginSetting::query()->updateOrCreate(
                ['provider' => $provider],
                [
                    'client_id' => null,
                    'client_secret' => null,
                    'redirect_url' => url('/auth/' . $provider . '/callback'),
                    'is_enabled' => false,
                ],
            );
        }
    }
}