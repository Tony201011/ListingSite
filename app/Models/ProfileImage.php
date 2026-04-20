<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class ProfileImage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'image_path',
        'thumbnail_path',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    protected $appends = [
        'image_url',
        'thumbnail_url',
    ];

    protected function imageUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->image_path) {
                    return null;
                }

                if (str_starts_with($this->image_path, 'http')) {
                    return $this->image_path;
                }

                return Storage::disk(config('media.delivery_disk'))->url($this->image_path);
            }
        );
    }

    protected function thumbnailUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (! $this->thumbnail_path) {
                    return $this->image_url;
                }

                if (str_starts_with($this->thumbnail_path, 'http')) {
                    return $this->thumbnail_path;
                }

                return Storage::disk(config('media.delivery_disk'))->url($this->thumbnail_path);
            }
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
