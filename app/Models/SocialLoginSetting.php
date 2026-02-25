<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocialLoginSetting extends Model
{
    use HasFactory;

    public const PROVIDER_GOOGLE = 'google';
    public const PROVIDER_FACEBOOK = 'facebook';
    public const PROVIDER_TWITTER = 'twitter';

    protected $fillable = [
        'provider',
        'client_id',
        'client_secret',
        'redirect_url',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
        ];
    }
}