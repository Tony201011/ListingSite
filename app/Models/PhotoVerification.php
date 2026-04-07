<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PhotoVerification extends Model
{
    use SoftDeletes;

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
        'photo_url',
    ];

    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (empty($this->photos) || ! is_array($this->photos)) {
                    return null;
                }

                $firstPhoto = $this->photos[0] ?? null;

                if (! $firstPhoto || ! is_array($firstPhoto)) {
                    return null;
                }

                $path = $firstPhoto['path'] ?? null;
                if ($path) {
                    return route('media.show', ['path' => $path]);
                }

                return $firstPhoto['url'] ?? null;
            }
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
