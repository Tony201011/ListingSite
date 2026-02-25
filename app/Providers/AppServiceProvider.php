<?php

namespace App\Providers;

use App\Models\S3BucketSetting;
use App\Models\SmtpSetting;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;
use SocialiteProviders\Twitter\Provider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(function (SocialiteWasCalled $event): void {
            $event->extendSocialite('twitter-oauth-2', Provider::class);
        });

        $this->configureSmtpFromDatabase();
        $this->configureStorageFromDatabase();
    }

    private function configureSmtpFromDatabase(): void
    {
        if (! Schema::hasTable('smtp_settings')) {
            return;
        }

        $setting = SmtpSetting::query()
            ->where('is_enabled', true)
            ->latest('updated_at')
            ->first();

        if (! $setting) {
            return;
        }

        Config::set('mail.default', $setting->mailer ?: 'smtp');
        Config::set('mail.mailers.smtp.host', $setting->host);
        Config::set('mail.mailers.smtp.port', $setting->port);
        Config::set('mail.mailers.smtp.encryption', $setting->encryption ?: null);
        Config::set('mail.mailers.smtp.username', $setting->username ?: null);
        Config::set('mail.mailers.smtp.password', $setting->password ?: null);
        Config::set('mail.from.address', $setting->from_address);
        Config::set('mail.from.name', $setting->from_name ?: config('app.name'));

        app('mail.manager')->forgetMailers();
    }

    private function configureStorageFromDatabase(): void
    {
        if (! Schema::hasTable('s3_bucket_settings')) {
            return;
        }

        $setting = S3BucketSetting::query()->latest('updated_at')->first();

        if (! $setting || ! $setting->is_enabled) {
            Config::set('filesystems.default', 'public');

            app('filesystem')->forgetDisk('public');

            return;
        }

        Config::set('filesystems.default', 's3');
        Config::set('filesystems.cloud', 's3');
        Config::set('filesystems.disks.s3.key', $setting->key);
        Config::set('filesystems.disks.s3.secret', $setting->secret);
        Config::set('filesystems.disks.s3.region', $setting->region);
        Config::set('filesystems.disks.s3.bucket', $setting->bucket);
        Config::set('filesystems.disks.s3.url', $setting->url);
        Config::set('filesystems.disks.s3.endpoint', $setting->endpoint);
        Config::set('filesystems.disks.s3.use_path_style_endpoint', $setting->use_path_style_endpoint);

        app('filesystem')->forgetDisk('s3');
        app('filesystem')->forgetDisk('public');
    }
}
