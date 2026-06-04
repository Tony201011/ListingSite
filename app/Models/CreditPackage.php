<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreditPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'credits',
        'bonus_credits',
        'price',
        'currency',
        'description',
        'status',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'credits' => 'integer',
        'bonus_credits' => 'integer',
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

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

        return $active === null ? $this->status === 'active' : (bool) $active;
    }

    public function getTotalCreditsAttribute(): int
    {
        return (int) $this->credits + (int) $this->bonus_credits;
    }
}
