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
    ];

    protected $casts = [
        'enable_cookies' => 'boolean',
        'captcha_enabled' => 'boolean',
        'site_password_enabled' => 'boolean',
        'site_password' => 'encrypted',
        'short_url' => 'boolean',
    ];
}
