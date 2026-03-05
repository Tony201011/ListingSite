<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PricingPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'subtitle',
        'intro_content',
        'packages_title',
        'packages_content',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function packages(): HasMany
    {
        return $this->hasMany(PricingPackage::class)
            ->orderBy('sort_order')
            ->orderBy('id');
    }
}
