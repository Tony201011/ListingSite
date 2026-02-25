<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class S3BucketSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'secret',
        'region',
        'bucket',
        'url',
        'endpoint',
        'use_path_style_endpoint',
        'is_enabled',
    ];

    protected function casts(): array
    {
        return [
            'use_path_style_endpoint' => 'boolean',
            'is_enabled' => 'boolean',
        ];
    }
}