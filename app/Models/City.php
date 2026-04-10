<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;

class City extends Model
{
    use HasFactory;

    protected $fillable = [
        'state_id',
        'name',
    ];

    protected static function booted(): void
    {
        $clearCache = fn () => Cache::forget('header_escort_cities');

        static::saved($clearCache);
        static::deleted($clearCache);
    }

    public function state(): BelongsTo
    {
        return $this->belongsTo(State::class);
    }
}
