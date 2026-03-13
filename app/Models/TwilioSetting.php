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
        'dummy_mode_enabled',
        'dummy_mobile_number',
        'dummy_otp',
    ];

    protected function casts(): array
    {
        return [
            'dummy_mode_enabled' => 'boolean',
        ];
    }
}
