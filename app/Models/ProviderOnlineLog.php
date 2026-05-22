<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderOnlineLog extends Model
{
    protected $fillable = [
        'user_id',
        'provider_profile_id',
        'went_online_at',
        'went_offline_at',
        'duration_seconds',
    ];

    protected $casts = [
        'went_online_at' => 'datetime',
        'went_offline_at' => 'datetime',
        'duration_seconds' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }
}
