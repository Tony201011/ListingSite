<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class UserVideo extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'video_path',
        'original_name',
    ];

    protected $appends = ['video_url'];

    public function getVideoUrlAttribute(): ?string
    {
        return $this->video_path
            ? Storage::disk('r2')->url($this->video_path)
            : null;
    }
}
