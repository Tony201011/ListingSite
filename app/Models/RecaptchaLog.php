<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecaptchaLog extends Model
{
    protected $fillable = [
        'action',
        'ip_address',
        'status',
        'error_codes',
        'hostname',
    ];

    protected function casts(): array
    {
        return [
            'error_codes' => 'array',
        ];
    }
}
