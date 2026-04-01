<?php

namespace App\Models;

use Illuminate\Filesystem\FilesystemAdapter;
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
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('media.delivery_disk'));

        return Attribute::make(
            get: fn () => $this->image_path
                ? $disk->url($this->image_path)
                : null
        );
    }

    protected function thumbnailUrl(): Attribute
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('media.delivery_disk'));

        return Attribute::make(
            get: fn () => $this->thumbnail_path
                ? $disk->url($this->thumbnail_path)
                : null
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
