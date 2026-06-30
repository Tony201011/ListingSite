<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CreditPurchase extends Model
{
    protected static function booted(): void
    {
        static::creating(function (self $purchase): void {
            if (empty($purchase->uuid)) {
                $purchase->uuid = (string) Str::uuid();
            }
        });
    }
    protected $fillable = [
        'uuid',
        'user_id',
        'provider_profile_id',
        'credit_package_id',
        'credits',
        'amount_cents',
        'currency',
        'status',
        'woo_order_id',
        'paid_at',
        'purchase_transaction_id',
    ];

    protected $casts = [
        'credits' => 'integer',
        'amount_cents' => 'integer',
        'provider_profile_id' => 'integer',
        'woo_order_id' => 'integer',
        'purchase_transaction_id' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function providerProfile(): BelongsTo
    {
        return $this->belongsTo(ProviderProfile::class);
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(CreditPackage::class, 'credit_package_id');
    }

    public function purchaseTransaction(): BelongsTo
    {
        return $this->belongsTo(PurchaseTransaction::class, 'purchase_transaction_id');
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
