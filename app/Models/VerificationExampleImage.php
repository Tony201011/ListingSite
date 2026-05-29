<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VerificationExampleImage extends Model
{
    protected $table = 'verification_example_images';

    protected $fillable = [
        'label',
        'image_url',
        'image_path',
        'caption',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Returns the resolved image URL.
     * Prefers an uploaded file (image_path) over a manually entered external URL.
     */
    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function (mixed $value): ?string {
                if (filled($this->attributes['image_path'] ?? null)) {
                    return Storage::disk('public')->url($this->attributes['image_path']);
                }

                return $value;
            },
        );
    }
}
