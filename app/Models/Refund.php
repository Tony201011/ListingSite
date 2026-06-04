<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable = [
        'payment_id',
        'user_id',
        'amount',
        'refunded_credits',
        'provider_refund_id',
        'reason',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refunded_credits' => 'integer',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(PurchaseTransaction::class, 'payment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
