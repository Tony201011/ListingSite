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
        'online_status_max_uses',
        'online_status_duration_minutes',
        'available_now_max_uses',
        'available_now_duration_minutes',
        'fatal_error_page_enabled',
        'fatal_error_default_message',
        'fatal_error_query_param',
        'logging_enabled',
        'max_video_upload_mb',
    ];

    protected $casts = [
        'enable_cookies' => 'boolean',
        'captcha_enabled' => 'boolean',
        'site_password_enabled' => 'boolean',
        'short_url' => 'boolean',
        'max_search_distance' => 'integer',
        'distance_search_enabled' => 'boolean',
        'home_page_records' => 'integer',
        'online_status_max_uses' => 'integer',
        'online_status_duration_minutes' => 'integer',
        'available_now_max_uses' => 'integer',
        'available_now_duration_minutes' => 'integer',
        'fatal_error_page_enabled' => 'boolean',
        'logging_enabled' => 'boolean',
        'max_video_upload_mb' => 'integer',
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

    protected static function booted(): void
    {
        static::saved(function (): void {
            cache()->forget('site_setting.home_page_records');
            cache()->forget('site_setting.status_settings');
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
}
