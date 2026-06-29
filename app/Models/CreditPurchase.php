<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditPurchase extends Model
{
    protected $fillable = [
        'uuid',
        'user_id',
        'credit_package_id',
        'credits',
        'amount_cents',
        'currency',
        'status',
        'woo_order_id',
        'paid_at',
    ];

    protected $casts = [
        'credits' => 'integer',
        'amount_cents' => 'integer',
        'woo_order_id' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(CreditPackage::class, 'credit_package_id');
    }

    public function getAmountAttribute(): float
    {
        return $this->amount_cents / 100;
    }

    public function getFormattedAmountAttribute(): string
    {
        return $this->currency . ' $' . number_format($this->amount, 2);
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }
}
