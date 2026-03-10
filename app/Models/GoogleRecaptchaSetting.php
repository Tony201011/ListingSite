<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleRecaptchaSetting extends Model
{
    protected $fillable = [
        'domain',
        'site_key',
        'secret_key',
        'is_active',
    ];
}
