<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class UserVideo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'video_path',
        'original_name',
    ];

    protected $appends = ['video_url'];

    public function getVideoUrlAttribute(): ?string
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk(config('media.delivery_disk'));

        return $this->video_path
            ? $disk->url($this->video_path)
            : null;
    }
}
