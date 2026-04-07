<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        return $this->video_path
            ? route('media.show', ['path' => $this->video_path])
            : null;
    }
}
