<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PhotoVerification extends Model
{
    protected $fillable = [
        'user_id',
        'photos',
        'status',
        'admin_note',
        'submitted_at',
    ];

    protected $casts = [
        'photos' => 'array',
        'submitted_at' => 'datetime',
    ];


    protected $appends = [
        'photo_urls'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
