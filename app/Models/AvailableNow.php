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
        return $this->status === 'online'
            && $this->available_expires_at
            && now()->lt($this->available_expires_at);
    }

    public function resetDailyUsageIfNeeded(): void
    {
        if (! $this->usage_date || ! $this->usage_date->isToday()) {
            $this->usage_date = today();
            $this->usage_count = 0;
            $this->status = 'offline';
            $this->available_started_at = null;
            $this->available_expires_at = null;
        }
    }
}
