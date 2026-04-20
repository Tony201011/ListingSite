<?php

namespace App\Providers;

use App\Listeners\LogPasswordResetNotificationEmail;
use App\Listeners\RecordUserLogin;
use App\Models\FooterText;
use App\Models\FooterWidget;
use App\Models\HeaderWidget;
use App\Models\Postcode;
use App\Models\S3BucketSetting;
use App\Models\SmtpSetting;
use App\Notifications\BrandedAgentResetPasswordNotification;
use Filament\Auth\Notifications\ResetPassword as FilamentResetPasswordNotification;
use Filament\Support\Facades\FilamentView;
use Illuminate\Auth\Events\Login;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(FilamentResetPasswordNotification::class, BrandedAgentResetPasswordNotification::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        FilamentView::registerRenderHook(
            'panels::head.end',
            fn (): string => '<style>.fi-sidebar{min-height:0!important;}.fi-body-has-topbar .fi-sidebar{max-height:calc(100dvh - 4rem)!important;}.fi-sidebar-nav{min-height:0!important;overflow-y:auto!important;overscroll-behavior:contain!important;scrollbar-gutter:stable;}</style>',
        );

        FilamentView::registerRenderHook(
            'panels::body.end',
            fn (): string => '<script src="https://cdn.ckeditor.com/ckeditor5/40.2.0/classic/ckeditor.js"></script>',
        );

        $this->shareFooterText();
        $this->configureSmtpFromDatabase();
        $this->configureStorageFromDatabase();

        Event::listen(Login::class, RecordUserLogin::class);
        Event::listen(NotificationSent::class, LogPasswordResetNotificationEmail::class);
        Event::listen(NotificationFailed::class, LogPasswordResetNotificationEmail::class);
    }

    private function shareFooterText(): void
    {
        View::composer('layouts.partials.footer', function ($view): void {
            if (! Schema::hasTable('footer_texts')) {
                $view->with('footerText', null);
                $view->with('footerWidget', null);

                return;
            }

            $footerText = FooterText::query()
                ->where('is_active', true)
                ->latest('updated_at')
                ->first();

            $footerWidget = Schema::hasTable('footer_widgets')
                ? FooterWidget::query()->where('is_active', true)->latest('updated_at')->first()
                : null;

            $view->with('footerText', $footerText);
            $view->with('footerWidget', $footerWidget);
        });

        View::composer('layouts.partials.header', function ($view): void {
            if (! Schema::hasTable('header_widgets')) {
                $view->with('headerWidget', null);
                $view->with('escortCities', collect());

                return;
            }

            $headerWidget = HeaderWidget::query()
                ->where('is_active', true)
                ->latest('updated_at')
                ->first();

            $escortCities = Schema::hasTable('postcodes')
                ? Cache::remember('header_escort_suburbs', now()->addHour(), function () {
                    // users.suburb stores the full "Suburb, STATE postcode" format
                    // (e.g. "Sydney, NSW 2000") written by the signup/edit-profile
                    // autocomplete.  We join postcodes on the suburb name prefix so that
                    // the header links include the state (e.g. "/?location=Sydney, NSW")
                    // and therefore only return providers from the correct state.
                    return Postcode::query()
                        ->select(['suburb', 'state'])
                        ->groupBy(['suburb', 'state'])
                        ->whereExists(function ($q) {
                            $q->selectRaw('1')
                                ->from('users')
                                ->whereNotNull('suburb')
                                ->where('suburb', '!=', '')
                                ->whereRaw('users.suburb LIKE CONCAT(postcodes.suburb, \', \', postcodes.state, \'%\')')
                                ->whereExists(function ($q2) {
                                    $q2->selectRaw('1')
                                        ->from('provider_profiles')
                                        ->whereColumn('provider_profiles.user_id', 'users.id')
                                        ->where('provider_profiles.profile_status', 'approved')
                                        ->whereNull('provider_profiles.deleted_at');
                                });
                        })
                        ->orderBy('suburb')
                        ->get();
                })
                : collect();

            $view->with('headerWidget', $headerWidget);
            $view->with('escortCities', $escortCities);
        });
    }

    private function configureSmtpFromDatabase(): void
    {
        try {
            if (! Schema::hasTable('smtp_settings')) {
                return;
            }
        } catch (\Illuminate\Database\QueryException|\PDOException) {
            return;
        }

        $setting = SmtpSetting::query()
            ->where('is_enabled', true)
            ->latest('updated_at')
            ->first();

        if (! $setting) {
            return;
        }

        $sandboxDomain = $setting->mailgun_sandbox_domain ?: $setting->mailgun_domain;
        $liveDomain = $setting->mailgun_live_domain;
        $mailgunDomain = $setting->use_mailgun_sandbox
            ? ($sandboxDomain ?: env('MAILGUN_DOMAIN'))
            : ($liveDomain ?: $sandboxDomain ?: env('MAILGUN_DOMAIN'));
        $mailgunEndpoint = $setting->mailgun_endpoint ?: env('MAILGUN_ENDPOINT', 'api.mailgun.net');

        if (filled($mailgunDomain)) {
            $mailgunDomain = preg_replace('#^https?://#i', '', rtrim(trim($mailgunDomain), '/'));
        }

        if (filled($mailgunEndpoint)) {
            $mailgunEndpoint = parse_url(trim($mailgunEndpoint), PHP_URL_HOST)
                ?: preg_replace('#^https?://#i', '', rtrim(trim($mailgunEndpoint), '/'));
        }

        Config::set('mail.default', $setting->mail_mailer ?: 'mailgun');
        Config::set('services.mailgun.domain', $mailgunDomain);
        Config::set('services.mailgun.secret', $setting->mailgun_secret ?: env('MAILGUN_SECRET'));
        Config::set('services.mailgun.endpoint', $mailgunEndpoint ?: 'api.mailgun.net');
        Config::set('mail.from.address', $setting->mail_from_address ?: env('MAIL_FROM_ADDRESS'));
        Config::set('mail.from.name', $setting->mail_from_name ?: env('MAIL_FROM_NAME', config('app.name')));

        app('mail.manager')->forgetMailers();
    }

    private function configureStorageFromDatabase(): void
    {
        try {
            if (! Schema::hasTable('s3_bucket_settings')) {
                return;
            }
        } catch (\Illuminate\Database\QueryException|\PDOException) {
            return;
        }

        $setting = S3BucketSetting::query()->latest('updated_at')->first();

        if (! $setting || ! $setting->is_enabled) {
            Config::set('filesystems.default', 'public');

            if (! env('MEDIA_UPLOAD_DISK')) {
                Config::set('media.upload_disk', 'public');
            }

            if (! env('MEDIA_DELIVERY_DISK')) {
                Config::set('media.delivery_disk', 'public');
            }

            if (! env('AVATAR_DISK')) {
                Config::set('media.avatar_disk', 'public');
            }

            app('filesystem')->forgetDisk('public');

            return;
        }

        Config::set('filesystems.default', 's3');
        Config::set('filesystems.cloud', 's3');
        Config::set('filesystems.disks.s3.key', env('AWS_ACCESS_KEY_ID'));
        Config::set('filesystems.disks.s3.secret', env('AWS_SECRET_ACCESS_KEY'));
        Config::set('filesystems.disks.s3.region', $setting->region);
        Config::set('filesystems.disks.s3.bucket', $setting->bucket);
        Config::set('filesystems.disks.s3.url', $setting->url);
        Config::set('filesystems.disks.s3.endpoint', $setting->endpoint);
        Config::set('filesystems.disks.s3.use_path_style_endpoint', $setting->use_path_style_endpoint);

        if (! env('MEDIA_UPLOAD_DISK')) {
            Config::set('media.upload_disk', 's3');
        }

        if (! env('MEDIA_DELIVERY_DISK')) {
            Config::set('media.delivery_disk', 's3');
        }

        if (! env('AVATAR_DISK')) {
            Config::set('media.avatar_disk', 'public');
        }

        app('filesystem')->forgetDisk('s3');
        app('filesystem')->forgetDisk('public');

        // Override temporary URL generation so Filament's FileUpload previews
        // are served through the Laravel proxy instead of directly from S3/R2,
        // avoiding browser CORS errors.
        \Illuminate\Support\Facades\Storage::disk('s3')->buildTemporaryUrlsUsing(
            fn (string $path, \DateTimeInterface $expiration, array $options): string => route('media.show', ['path' => $path])
        );
    }
}
