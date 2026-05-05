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
        'price',
        'description',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'credits' => 'integer',
        'price' => 'decimal:2',
        'sort_order' => 'integer',
    ];

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }
}
