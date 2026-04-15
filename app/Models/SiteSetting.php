<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'home_page_records',
        'online_status_max_uses',
        'online_status_duration_minutes',
        'available_now_max_uses',
        'available_now_duration_minutes',
        'fatal_error_page_enabled',
        'fatal_error_default_message',
        'fatal_error_query_param',
    ];

    protected $casts = [
        'enable_cookies' => 'boolean',
        'captcha_enabled' => 'boolean',
        'site_password_enabled' => 'boolean',
        'site_password' => 'encrypted',
        'short_url' => 'boolean',
        'max_search_distance' => 'integer',
        'home_page_records' => 'integer',
        'online_status_max_uses' => 'integer',
        'online_status_duration_minutes' => 'integer',
        'available_now_max_uses' => 'integer',
        'available_now_duration_minutes' => 'integer',
        'fatal_error_page_enabled' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (): void {
            cache()->forget('site_setting.home_page_records');
            cache()->forget('site_setting.status_settings');
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
}
