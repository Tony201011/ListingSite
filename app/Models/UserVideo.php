<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


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
        // return $this->video_path
        //     ? Storage::disk('s3')->url($this->video_path)
        //     : null;

        return $this->video_path
            ? 'https://pub-4e37ec8f58e94a569d35a5245489f90d.r2.dev/'.$this->video_path
            : null;
    }
}
