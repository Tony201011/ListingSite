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
        'cookies_text',
    ];
}
