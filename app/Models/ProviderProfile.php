<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'age',
        'description',
        'country_id',
        'state_id',
        'city_id',
        'latitude',
        'longitude',
        'phone',
        'whatsapp',
        'is_verified',
        'is_featured',
        'membership_id',
        'profile_status',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'age' => 'integer',
            'country_id' => 'integer',
            'state_id' => 'integer',
            'city_id' => 'integer',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'is_verified' => 'boolean',
            'is_featured' => 'boolean',
            'membership_id' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}