<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineUser extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'status',
        'usage_date',
        'usage_count',
        'online_started_at',
        'online_expires_at',
    ];

    protected $casts = [
        'status' => 'string',
        'usage_date' => 'date',
        'online_started_at' => 'datetime',
        'online_expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function isCurrentlyOnline(): bool
    {
        return $this->status === 'online'
            && $this->online_expires_at
            && now()->lessThan($this->online_expires_at);
    }

    public function resetDailyUsageIfNeeded(): void
    {
        if (! $this->usage_date || ! $this->usage_date->isToday()) {
            $this->usage_date = today();
            $this->usage_count = 0;
            $this->status = 'offline';
            $this->online_started_at = null;
            $this->online_expires_at = null;
        }
    }
}
