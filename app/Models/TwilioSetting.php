<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TwilioSetting extends Model
{
    protected $fillable = [
        'account_sid',
        'api_sid',
        'api_secret',
        'phone_number',
    ];
}
