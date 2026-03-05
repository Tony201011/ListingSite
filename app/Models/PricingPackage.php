<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PricingPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'pricing_page_id',
        'credits',
        'total_price',
        'price_per_credit',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function pricingPage(): BelongsTo
    {
        return $this->belongsTo(PricingPage::class);
    }
}
