<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VerificationExampleImage extends Model
{
    protected $table = 'verification_example_images';

    protected $fillable = [
        'label',
        'image_url',
        'caption',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];
}
