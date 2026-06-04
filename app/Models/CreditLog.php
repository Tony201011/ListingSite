<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CreditLog extends Model
{
    protected $fillable = [
        'user_id',
        'amount',
        'balance_after',
        'type',
        'transaction_type',
        'status',
        'description',
        'related_payment_id',
        'reference_type',
        'reference_id',
    ];

    protected $casts = [
        'amount' => 'integer',
        'balance_after' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function relatedPayment(): BelongsTo
    {
        return $this->belongsTo(PurchaseTransaction::class, 'related_payment_id');
    }

    public function getFormattedDateAttribute(): string
    {
        return $this->created_at->format('d M Y');
    }

    public function getMonthAttribute(): string
    {
        return $this->created_at->format('Y-m');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }
}
