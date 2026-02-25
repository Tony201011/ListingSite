<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmtpSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'mailer',
        'host',
        'port',
        'encryption',
        'username',
        'password',
        'from_address',
        'from_name',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'port' => 'integer',
            'is_enabled' => 'boolean',
        ];
    }
}