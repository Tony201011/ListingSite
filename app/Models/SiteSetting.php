<?php

namespace App\Models;

use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SiteSetting extends Model
{
    protected $table = 'site_settings';

    protected $fillable = [
        'meta_key',
        'meta_description',
        'enable_cookies',
        'captcha_enabled',
        'cookies_text',
        'site_password_enabled',
        'site_password',
        'contact_email',
        'short_url',      // new field for short URL feature
        'max_search_distance',
        'distance_search_enabled',
        'home_page_records',
        'online_filter_enabled',
        'online_status_max_uses',
        'online_status_duration_minutes',
        'available_now_max_uses',
        'available_now_duration_minutes',
        'featured_credit_cost',
        'featured_duration_days',
        'free_listing_days',
        'home_banner_credit_cost',
        'home_featured_credit_cost',
        'local_banner_credit_cost',
        'fatal_error_page_enabled',
        'fatal_error_default_message',
        'fatal_error_query_param',
        'logging_enabled',
        'max_video_upload_mb',
        'stripe_mode',
        'stripe_publishable_key',
        'stripe_secret_key',
        'stripe_webhook_secret',
        'stripe_enabled',
    ];

    protected $casts = [
        'enable_cookies' => 'boolean',
        'captcha_enabled' => 'boolean',
        'site_password_enabled' => 'boolean',
        'short_url' => 'boolean',
        'max_search_distance' => 'integer',
        'distance_search_enabled' => 'boolean',
        'home_page_records' => 'integer',
        'online_filter_enabled' => 'boolean',
        'online_status_max_uses' => 'integer',
        'online_status_duration_minutes' => 'integer',
        'available_now_max_uses' => 'integer',
        'available_now_duration_minutes' => 'integer',
        'featured_credit_cost' => 'integer',
        'featured_duration_days' => 'integer',
        'free_listing_days' => 'integer',
        'home_banner_credit_cost' => 'integer',
        'home_featured_credit_cost' => 'integer',
        'local_banner_credit_cost' => 'integer',
        'fatal_error_page_enabled' => 'boolean',
        'logging_enabled' => 'boolean',
        'max_video_upload_mb' => 'integer',
        'stripe_mode' => 'string',
        'stripe_enabled' => 'boolean',
    ];

    protected function sitePassword(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?string {
                if ($value === null) {
                    return null;
                }
                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException $e) {
                    logger()->warning('SiteSetting: failed to decrypt site_password, treating as plain text (key rotation may be needed).', ['exception' => $e->getMessage()]);

                    return $value;
                }
            },
            set: fn (?string $value): ?string => ($value !== null && $value !== '') ? Crypt::encryptString($value) : null,
        );
    }

    protected function stripeSecretKey(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?string {
                if ($value === null) {
                    return null;
                }
                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException $e) {
                    logger()->warning('SiteSetting: failed to decrypt stripe_secret_key, treating as plain text (key rotation may be needed).', ['exception' => $e->getMessage()]);

                    return $value;
                }
            },
            set: fn (?string $value): ?string => ($value !== null && $value !== '') ? Crypt::encryptString($value) : null,
        );
    }

    protected function stripeWebhookSecret(): Attribute
    {
        return Attribute::make(
            get: function (?string $value): ?string {
                if ($value === null) {
                    return null;
                }
                try {
                    return Crypt::decryptString($value);
                } catch (DecryptException $e) {
                    logger()->warning('SiteSetting: failed to decrypt stripe_webhook_secret, treating as plain text (key rotation may be needed).', ['exception' => $e->getMessage()]);

                    return $value;
                }
            },
            set: fn (?string $value): ?string => ($value !== null && $value !== '') ? Crypt::encryptString($value) : null,
        );
    }

    protected static function booted(): void
    {
        static::saved(function (): void {
            cache()->forget('site_setting.home_page_records');
            cache()->forget('site_setting.online_filter_enabled');
            cache()->forget('site_setting.status_settings');
            cache()->forget('site_setting.featured_settings');
            cache()->forget('site_setting.ad_tier_settings');
            cache()->forget('site_setting.logging_enabled');
            cache()->forget('site_setting.site_password_config');
            cache()->forget('site_setting.max_video_upload_mb');
        });
    }

    public static function getSitePasswordConfig(): array
    {
        return cache()->remember('site_setting.site_password_config', now()->addMinutes(10), function () {
            $setting = static::first();

            if (! $setting) {
                return ['enabled' => false, 'password' => null];
            }

            return [
                'enabled' => (bool) $setting->site_password_enabled,
                'password' => $setting->site_password,
            ];
        });
    }

    public static function isLoggingEnabled(): bool
    {
        return cache()->remember('site_setting.logging_enabled', now()->addMinutes(10), function () {
            return (bool) (static::first()?->logging_enabled ?? true);
        });
    }

    public static function isOnlineFilterEnabled(): bool
    {
        return cache()->remember('site_setting.online_filter_enabled', now()->addMinutes(10), function () {
            return (bool) (static::first()?->online_filter_enabled ?? false);
        });
    }

    public static function getStatusSettings(): array
    {
        return cache()->remember('site_setting.status_settings', now()->addMinutes(10), function () {
            $setting = static::first();

            return [
                'online_status_max_uses' => $setting?->online_status_max_uses ?? 4,
                'online_status_duration_minutes' => $setting?->online_status_duration_minutes ?? 60,
                'available_now_max_uses' => $setting?->available_now_max_uses ?? 2,
                'available_now_duration_minutes' => $setting?->available_now_duration_minutes ?? 120,
            ];
        });
    }

    public static function getMaxVideoUploadMb(): int
    {
        return cache()->remember('site_setting.max_video_upload_mb', now()->addMinutes(10), function () {
            return (int) (static::first()?->max_video_upload_mb ?: 100);
        });
    }

    public static function getFeaturedSettings(): array
    {
        return cache()->remember('site_setting.featured_settings', now()->addMinutes(10), function () {
            $setting = static::first();

            return [
                'featured_credit_cost' => $setting?->featured_credit_cost ?? 5,
                'featured_duration_days' => $setting?->featured_duration_days ?? 1,
            ];
        });
    }

    public static function getAdTierSettings(): array
    {
        return cache()->remember('site_setting.ad_tier_settings', now()->addMinutes(10), function () {
            $setting = static::first();

            return [
                'free_listing_days' => $setting?->free_listing_days ?? 21,
                'featured_duration_days' => 1,
                // Daily credit costs per tier
                'normal_featured_credit_cost' => $setting?->featured_credit_cost ?? 1,
                'home_featured_credit_cost' => $setting?->home_featured_credit_cost ?? 3,
                'local_banner_credit_cost' => $setting?->local_banner_credit_cost ?? 2,
                'home_banner_credit_cost' => $setting?->home_banner_credit_cost ?? 5,
            ];
        });
    }
}
