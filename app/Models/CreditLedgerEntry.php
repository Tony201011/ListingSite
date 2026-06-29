<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CreditLedgerEntry extends Model
{
    protected $fillable = [
        'user_id',
        'credit_purchase_id',
        'type',
        'credits_delta',
        'source_type',
        'source_id',
        'description',
    ];

    protected $casts = [
        'credits_delta' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(CreditPurchase::class, 'credit_purchase_id');
    }

    public function isCredit(): bool
    {
        return $this->credits_delta > 0;
    }

    public function isDebit(): bool
    {
        return $this->credits_delta < 0;
    }
}
