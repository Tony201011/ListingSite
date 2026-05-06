<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Ad extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'image_path',
        'link_url',
        'position',
        'page_keys',
        'is_active',
        'open_in_new_tab',
        'sort_order',
        'starts_at',
        'ends_at',
    ];

    protected function casts(): array
    {
        return [
            'page_keys' => 'array',
            'is_active' => 'boolean',
            'open_in_new_tab' => 'boolean',
            'sort_order' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    public function getImageUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    /**
     * Return active ads for a given position and page key.
     *
     * @return Collection<int, Ad>
     */
    public static function forPositionAndPage(string $position, string $pageKey): Collection
    {
        return static::active()
            ->where('position', $position)
            ->where(function (Builder $q) use ($pageKey): void {
                $q->whereNull('page_keys')
                    ->orWhereJsonContains('page_keys', $pageKey)
                    ->orWhereJsonContains('page_keys', 'all-pages');
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();
    }
}
