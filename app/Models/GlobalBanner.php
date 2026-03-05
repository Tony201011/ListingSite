<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalBanner extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (GlobalBanner $banner): void {
            $pageKeys = collect($banner->page_keys ?? [])
                ->filter(fn ($key) => filled($key))
                ->map(fn ($key) => trim((string) $key))
                ->unique()
                ->values();

            if ($pageKeys->isEmpty()) {
                if (is_array($banner->page_key)) {
                    $pageKeys = collect($banner->page_key)
                        ->filter(fn ($key) => filled($key))
                        ->map(fn ($key) => trim((string) $key))
                        ->unique()
                        ->values();
                } elseif (filled($banner->page_key)) {
                    $pageKeys = collect([trim((string) $banner->page_key)]);
                }
            }

            if ($pageKeys->contains('all-pages')) {
                $pageKeys = collect(['all-pages']);
            }

            $banner->page_keys = $pageKeys->all();
            $banner->page_key = $pageKeys->first();
        });
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
