<?php

namespace App\Models;

use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalBanner extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (GlobalBanner $banner): void {
            $pageKeys = self::normalizePageKeys($banner->page_keys);

            if ($pageKeys->isEmpty()) {
                $pageKeys = self::normalizePageKeys($banner->page_key);
            }

            if ($pageKeys->contains('all-pages')) {
                $pageKeys = collect(['all-pages']);
            }

            $banner->page_keys = $pageKeys->all();
            $banner->page_key = $pageKeys->first();
        });
    }

    private static function normalizePageKeys(mixed $state): Collection
    {
        return collect((array) $state)
            ->flatMap(function ($value, $key): array {
                if (! is_int($key)) {
                    return filled($value) && $value !== false
                        ? [trim((string) $key)]
                        : [];
                }

                return filled($value)
                    ? [trim((string) $value)]
                    : [];
            })
            ->filter(fn (string $key): bool => $key !== '')
            ->unique()
            ->values();
    }

    protected $fillable = [
        'page_key',
        'page_keys',
        'banner_image_path',
        'banner_title',
        'banner_subtitle',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'page_keys' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
