<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'referrer_id',
        'referred_user_id',
        'referral_code',
        'status',
        'reward_amount',
        'rewarded_at',
        'payment_id',
    ];

    protected $casts = [
        'reward_amount' => 'decimal:2',
        'rewarded_at' => 'datetime',
    ];

    public function referrer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referrer_id');
    }

    public function referredUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_user_id');
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(PurchaseTransaction::class, 'payment_id');
    }
}
