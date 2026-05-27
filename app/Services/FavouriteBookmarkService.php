<?php

namespace App\Services;

use App\Models\ProviderProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

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
        return $this->getNormalizedProfileIds('favourites');
    }

    public function getBookmarks(): array
    {
        return $this->getNormalizedProfileIds('bookmarks');
    }

    public function slugExists(string $slug): bool
    {
        return $this->resolveProfileId($slug) !== null;
    }

    private function toggle(string $type, string $slug): bool
    {
        $key = $this->cacheKey($type);
        $profileId = $this->resolveProfileId($slug);
        if ($profileId === null) {
            return false;
        }

        $profileIds = $this->getNormalizedProfileIds($type);
        $profileId = (string) $profileId;

        if (in_array($profileId, $profileIds, true)) {
            $profileIds = array_values(array_filter($profileIds, fn ($id) => $id !== $profileId));
            $active = false;
        } else {
            $profileIds[] = $profileId;
            $active = true;
        }

        Cache::put($key, $profileIds, self::TTL_SECONDS);

        return $active;
    }

    private function cacheKey(string $type): string
    {
        if (auth()->check()) {
            return "{$type}_user_".auth()->id();
        }

        return "{$type}_sess_".session()->getId();
    }

    private function getNormalizedProfileIds(string $type): array
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
            if ($this->isNumericId($normalized)) {
                $numericIds[] = (int) $normalized;
            }
        }

        $validIds = empty($numericIds)
            ? collect()
            : ProviderProfile::query()
                ->whereNull('deleted_at')
                ->where('profile_status', 'approved')
                ->where('is_blocked', false)
                ->whereIn('id', array_unique($numericIds))
                ->pluck('id')
                ->map(fn ($id) => (string) $id)
                ->flip();

        $textSlugs = [];
        foreach ($cached as $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $normalized = trim((string) $value);
            if ($normalized === '' || $this->isNumericId($normalized)) {
                continue;
            }

            $textSlugs[] = mb_strtolower($normalized);
        }

        $canonicalSlugIds = empty($textSlugs)
            ? collect()
            : ProviderProfile::query()
                ->whereNull('deleted_at')
                ->where('profile_status', 'approved')
                ->where('is_blocked', false)
                ->whereIn(DB::raw('LOWER(slug)'), array_values(array_unique($textSlugs)))
                ->orderBy('profile_sequence')
                ->orderBy('id')
                ->get(['id', 'slug'])
                ->groupBy(fn (ProviderProfile $profile) => mb_strtolower((string) $profile->slug))
                ->map(
                    fn (Collection $profiles): array => $profiles
                        ->pluck('id')
                        ->map(fn ($id) => (string) $id)
                        ->values()
                        ->all()
                );

        $seen = [];
        $normalizedIds = [];

        foreach ($cached as $value) {
            if (! is_scalar($value)) {
                continue;
            }

            $normalized = trim((string) $value);
            if ($normalized === '') {
                continue;
            }

            if ($this->isNumericId($normalized)) {
                if (! $validIds->has($normalized)) {
                    continue;
                }
                if (! isset($seen[$normalized])) {
                    $seen[$normalized] = true;
                    $normalizedIds[] = (string) $normalized;
                }
            } else {
                $resolvedIds = $canonicalSlugIds->get(mb_strtolower($normalized), []);
                if ($resolvedIds === []) {
                    continue;
                }

                foreach ($resolvedIds as $resolvedId) {
                    if (isset($seen[$resolvedId])) {
                        continue;
                    }

                    $seen[$resolvedId] = true;
                    $normalizedIds[] = $resolvedId;
                }
            }
        }

        if ($normalizedIds !== $cached) {
            Cache::put($key, $normalizedIds, self::TTL_SECONDS);
        }

        return $normalizedIds;
    }

    private function resolveProfileId(string $identifier): ?int
    {
        $normalized = trim($identifier);
        if ($normalized === '') {
            return null;
        }

        $query = ProviderProfile::query()
            ->whereNull('deleted_at')
            ->where('profile_status', 'approved')
            ->where('is_blocked', false);

        if ($this->isNumericId($normalized)) {
            return $query->where('id', (int) $normalized)->value('id');
        }

        return $query
            ->whereRaw('LOWER(slug) = ?', [mb_strtolower($normalized)])
            ->orderBy('profile_sequence')
            ->orderBy('id')
            ->value('id');
    }

    private function isNumericId(string $value): bool
    {
        return ctype_digit($value);
    }
}
