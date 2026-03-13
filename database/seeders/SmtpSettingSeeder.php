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
            ['id' => 1],
            [
                'mail_mailer' => 'mailgun',
                'mailgun_domain' => env('MAILGUN_DOMAIN', 'sandbox4b4ea9f5c65b430492a08fe893705064.mailgun.org'),
                'mailgun_secret' => env('MAILGUN_SECRET'),
                'mailgun_endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
                'mail_from_address' => env('MAIL_FROM_ADDRESS', 'no-reply@mail.hotescort.com.au'),
                'mail_from_name' => env('MAIL_FROM_NAME', config('app.name', 'HotEscort')),
                'is_enabled' => true,
            ],
        );
    }
}