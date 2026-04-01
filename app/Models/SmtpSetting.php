<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SmtpSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'mail_mailer',
        'mailgun_domain',
        'mailgun_sandbox_domain',
        'mailgun_live_domain',
        'use_mailgun_sandbox',
        'mailgun_secret',
        'mailgun_endpoint',
        'mail_from_address',
        'mail_from_name',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'use_mailgun_sandbox' => 'boolean',
        ];
    }
}
