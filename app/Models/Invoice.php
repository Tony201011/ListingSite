<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    protected $fillable = [
        'invoice_number',
        'user_id',
        'payment_id',
        'package_name',
        'credits',
        'amount',
        'currency',
        'tax_amount',
        'payment_provider',
        'payment_reference',
        'purchased_at',
    ];

    protected $casts = [
        'credits' => 'integer',
        'amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'purchased_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(PurchaseTransaction::class, 'payment_id');
    }
}
