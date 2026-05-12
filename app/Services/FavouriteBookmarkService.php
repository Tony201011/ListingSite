<?php

namespace App\Services;

use App\Models\ProviderProfile;
use Illuminate\Support\Facades\Cache;

class FavouriteBookmarkService
{
    private const TTL_SECONDS = 60 * 60 * 24 * 30; // 30 days

    public function toggleFavourite(string $slug): bool
    {
        return $this->toggle('favourites', $slug);
    }

    public function toggleBookmark(string $slug): bool
    {
        return $this->toggle('bookmarks', $slug);
    }

    public function getFavourites(): array
    {
        return $this->getNormalizedSlugs('favourites');
    }

    public function getBookmarks(): array
    {
        return $this->getNormalizedSlugs('bookmarks');
    }

    public function slugExists(string $slug): bool
    {
        return ProviderProfile::whereNull('deleted_at')
            ->where('profile_status', 'approved')
            ->where('is_blocked', false)
            ->where('slug', $slug)
            ->exists();
    }

    private function toggle(string $type, string $slug): bool
    {
        $key = $this->cacheKey($type);
        $slugs = $this->getNormalizedSlugs($type);

        if (in_array($slug, $slugs, true)) {
            $slugs = array_values(array_filter($slugs, fn ($s) => $s !== $slug));
            $active = false;
        } else {
            $slugs[] = $slug;
            $active = true;
        }

        Cache::put($key, $slugs, self::TTL_SECONDS);

        return $active;
    }

    private function cacheKey(string $type): string
    {
        if (auth()->check()) {
            return "{$type}_user_".auth()->id();
        }

        return "{$type}_sess_".session()->getId();
    }

    private function getNormalizedSlugs(string $type): array
    {
        $key = $this->cacheKey($type);
        $cached = Cache::get($key, []);

        if (! is_array($cached) || $cached === []) {
            return [];
        }

        $numericIds = [];
        foreach ($cached as $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $normalized = trim((string) $value);
            if ($normalized !== '' && ctype_digit($normalized)) {
                $numericIds[] = (int) $normalized;
            }
        }

        $idToSlug = empty($numericIds)
            ? collect()
            : ProviderProfile::query()
                ->whereNull('deleted_at')
                ->where('profile_status', 'approved')
                ->where('is_blocked', false)
                ->whereIn('id', array_values(array_unique($numericIds)))
                ->pluck('slug', 'id');

        $seen = [];
        $normalizedSlugs = [];

        foreach ($cached as $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $normalized = trim((string) $value);
            if ($normalized === '') {
                continue;
            }

            if (ctype_digit($normalized)) {
                $resolved = $idToSlug->get((int) $normalized);
                if (! is_string($resolved) || $resolved === '') {
                    continue;
                }
                $normalized = $resolved;
            }

            if (! isset($seen[$normalized])) {
                $seen[$normalized] = true;
                $normalizedSlugs[] = $normalized;
            }
        }

        if ($normalizedSlugs !== $cached) {
            Cache::put($key, $normalizedSlugs, self::TTL_SECONDS);
        }

        return $normalizedSlugs;
    }
}
