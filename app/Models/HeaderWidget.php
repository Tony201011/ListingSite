<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HeaderWidget extends Model
{
    use HasFactory;

    private const LEGACY_ROUTE_MAP = [
        '/my-profile-1' => '/my-profile',
        '/my-route' => '/my-profile',
        '/my-profile-2' => '/edit-profile',
    ];

    private const REMOVED_TOP_NAV_LABELS = [
        'contactsupport',
        'samplelisting',
        'sampleprofile',
        'samplelistingprofile',
        'browselistings',
    ];

    protected static function booted(): void
    {
        static::saving(function (HeaderWidget $headerWidget): void {
            $headerWidget->action_links = self::normalizeLegacyLinks($headerWidget->action_links);
            $headerWidget->main_nav_links = self::normalizeTopNavigationLinks($headerWidget->main_nav_links);
            $headerWidget->mobile_extra_links = self::normalizeHeaderSupportLinks($headerWidget->mobile_extra_links);
            $headerWidget->top_right_links = self::normalizeHeaderSupportLinks($headerWidget->top_right_links);

            $links = collect($headerWidget->main_nav_links ?? [])
                ->filter(fn ($item) => filled($item['label'] ?? null) && filled($item['url'] ?? null))
                ->values();

            $hasEscortLink = $links->contains(function ($item): bool {
                return self::normalizeLabel((string) ($item['label'] ?? '')) === 'escorts';
            });

            if (! $hasEscortLink) {
                $pricingIndex = $links->search(function ($item): bool {
                    return self::normalizeLabel((string) ($item['label'] ?? '')) === 'pricing';
                });

                $escortLink = [
                    'label' => 'Escorts',
                    'url' => route('escorts.search'),
                ];

                if ($pricingIndex === false) {
                    $links->push($escortLink);
                } else {
                    $links->splice($pricingIndex, 0, [$escortLink]);
                }
            }

            $hasPricingLink = $links->contains(function ($item): bool {
                $label = self::normalizeLabel((string) ($item['label'] ?? ''));
                $rawUrl = trim((string) ($item['url'] ?? ''));
                $path = parse_url($rawUrl, PHP_URL_PATH);
                $normalizedPath = '/'.ltrim((string) ($path ?? $rawUrl), '/');

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

    private static function normalizeLegacyLinks(mixed $links): array
    {
        return collect($links ?? [])->map(function ($item): array {
            if (! is_array($item)) {
                return [];
            }

            $url = trim((string) ($item['url'] ?? ''));

            if ($url === '') {
                return $item;
            }

            $path = parse_url($url, PHP_URL_PATH);
            $path = $path !== null ? '/'.ltrim((string) $path, '/') : '/'.ltrim($url, '/');

            foreach (self::LEGACY_ROUTE_MAP as $legacyPath => $newPath) {
                if ($path === $legacyPath) {
                    $item['url'] = url($newPath);
                    break;
                }
            }

            return $item;
        })->filter(fn ($item) => is_array($item) && ! empty($item))->values()->all();
    }

    private static function normalizeTopNavigationLinks(mixed $links): array
    {
        return collect(self::normalizeLegacyLinks($links))
            ->map(function (array $item): array {
                if (self::normalizeLabel((string) ($item['label'] ?? '')) === 'browselistings') {
                    $item['label'] = 'Escorts';
                    $item['url'] = route('escorts.search');
                }

                return $item;
            })
            ->reject(function (array $item): bool {
                return in_array(self::normalizeLabel((string) ($item['label'] ?? '')), self::REMOVED_TOP_NAV_LABELS, true);
            })
            ->unique(fn (array $item): string => self::normalizeLabel((string) ($item['label'] ?? '')).'|'.trim((string) ($item['url'] ?? '')))
            ->values()
            ->all();
    }

    private static function normalizeHeaderSupportLinks(mixed $links): array
    {
        return collect(self::normalizeLegacyLinks($links))
            ->reject(function (array $item): bool {
                return self::normalizeLabel((string) ($item['label'] ?? '')) === 'contactsupport';
            })
            ->unique(fn (array $item): string => self::normalizeLabel((string) ($item['label'] ?? '')).'|'.trim((string) ($item['url'] ?? '')))
            ->values()
            ->all();
    }

    private static function normalizeLabel(string $label): string
    {
        return preg_replace('/[^a-z0-9]+/', '', strtolower(trim($label))) ?? '';
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
        'show_free_trial_cta',
        'free_trial_cta_text',
        'free_trial_cta_url',
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
            'show_free_trial_cta' => 'boolean',
            'free_trial_cta_text' => 'string',
            'free_trial_cta_url' => 'string',
            'action_links' => 'array',
            'main_nav_links' => 'array',
            'mobile_extra_links' => 'array',
            'is_active' => 'boolean',
        ];
    }
}
