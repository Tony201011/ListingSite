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
        'provider_profile_id',
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
        'photo_urls',
    ];

    protected function photoUrl(): Attribute
    {
        return Attribute::make(
            get: function () {
                return $this->photo_urls[0] ?? null;
            }
        );
    }

    protected function photoUrls(): Attribute
    {
        return Attribute::make(
            get: function (): array {
                if (empty($this->photos) || ! is_array($this->photos)) {
                    return [];
                }

                return collect($this->photos)
                    ->map(function ($photo): ?string {
                        if (! is_array($photo)) {
                            return null;
                        }

                        $path = $photo['path'] ?? null;
                        if (filled($path)) {
                            return route('media.show', ['path' => $path]);
                        }

                        $url = $photo['url'] ?? null;

                        return filled($url) ? (string) $url : null;
                    })
                    ->filter()
                    ->values()
                    ->all();
            }
        );
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function providerProfile()
    {
        return $this->belongsTo(ProviderProfile::class);
    }
}
