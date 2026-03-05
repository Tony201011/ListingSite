<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderWidget extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (HeaderWidget $headerWidget): void {
            $links = collect($headerWidget->main_nav_links ?? [])
                ->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['url'] ?? null))
                ->values();

            $hasPricingLink = $links->contains(function ($item): bool {
                $label = strtolower(trim((string) ($item['label'] ?? '')));
                $rawUrl = trim((string) ($item['url'] ?? ''));
                $path = parse_url($rawUrl, PHP_URL_PATH);
                $normalizedPath = '/' . ltrim((string) ($path ?? $rawUrl), '/');

                return $label === 'pricing' || $normalizedPath === '/pricing';
            });

            if (! $hasPricingLink) {
                $links->push([
                    'label' => 'Pricing',
                    'url' => url('/pricing'),
                ]);
            }

            $headerWidget->main_nav_links = $links->all();
        });
    }

    protected $fillable = [
        'logo_type',
        'logo_path',
        'logo_max_width',
        'logo_max_height',
        'header_background_color',
        'header_height',
        'header_width',
        'brand_primary',
        'brand_accent',
        'enable_top_bar',
        'top_left_items',
        'top_right_links',
        'enable_search',
        'action_links',
        'main_nav_links',
        'mobile_extra_links',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'logo_type' => 'string',
            'logo_path' => 'string',
            'logo_max_width' => 'integer',
            'logo_max_height' => 'integer',
            'header_background_color' => 'string',
            'header_height' => 'integer',
            'header_width' => 'integer',
            'enable_top_bar' => 'boolean',
            'top_left_items' => 'array',
            'top_right_links' => 'array',
            'enable_search' => 'boolean',
            'action_links' => 'array',
            'main_nav_links' => 'array',
            'mobile_extra_links' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
