<?php

namespace Database\Seeders;

use App\Models\SmtpSetting;
use Illuminate\Database\Seeder;

class SmtpSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SmtpSetting::query()->updateOrCreate(
            ['mailer' => 'smtp'],
            [
                'host' => 'smtp.mailtrap.io',
                'port' => 587,
                'encryption' => 'tls',
                'username' => null,
                'password' => null,
                'from_address' => 'hello@example.com',
                'from_name' => config('app.name', 'Laravel'),
                'is_enabled' => false,
            ],
        );
    }
}