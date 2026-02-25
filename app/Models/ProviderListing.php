<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProviderListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'age',
        'category_id',
        'category',
        'website_type',
        'audience_score',
        'thumbnail',
        'is_live',
        'is_vip',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'audience_score' => 'decimal:2',
            'is_live' => 'boolean',
            'is_vip' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categoryItem(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }
}
