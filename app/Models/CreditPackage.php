<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreditPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'credits',
        'bonus_credits',
        'price',
        'currency',
        'description',
        'status',
        'is_active',
        'sort_order',
        'woo_product_id',
    ];

    protected $casts = [
        'credits' => 'integer',
        'bonus_credits' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'woo_product_id' => 'integer',
    ];

    public function purchases(): HasMany
    {
        return $this->hasMany(CreditPurchase::class);
    }

    public function scopeActive($query)
    {
        return $query->where(function ($builder): void {
            $builder->where('is_active', true)
                ->orWhere('status', 'active');
        });
    }

    public function getIsActiveAttribute(): bool
    {
        $active = $this->getAttributes()['is_active'] ?? null;

        return $active === null
            ? $this->status === 'active'
            : (bool) $active;
    }

    public function getTotalCreditsAttribute(): int
    {
        return (int) $this->credits + (int) $this->bonus_credits;
    }

    public function getFormattedPriceAttribute(): string
    {
        return $this->currency . ' $' . number_format((float) $this->price, 2);
    }

    public function getPriceCentsAttribute(): int
    {
        return (int) round((float) $this->price * 100);
    }

    public function hasWooProduct(): bool
    {
        return !empty($this->woo_product_id);
    }
}
