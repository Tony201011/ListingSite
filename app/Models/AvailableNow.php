<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AvailableNow extends Model
{
    use HasFactory;

    protected $table = 'available_nows';

    protected $fillable = [
        'user_id',
        'provider_profile_id',
        'status',
        'usage_date',
        'usage_count',
        'available_started_at',
        'available_expires_at',
    ];

    protected $casts = [
        'status' => 'string',
        'usage_date' => 'date',
        'available_started_at' => 'datetime',
        'available_expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isCurrentlyAvailable(): bool
    {
        return $this->status === 'online';
    }

    public function resetDailyUsageIfNeeded(): void
    {
        // Available Now is now a persistent manual status.
    }
}
