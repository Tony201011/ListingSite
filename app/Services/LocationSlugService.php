<?php

namespace App\Services;

use Illuminate\Support\Str;

class LocationSlugService
{
    private const STATE_MAP = [
        'ACT' => 'ACT',
        'NSW' => 'NSW',
        'VIC' => 'VIC',
        'QLD' => 'QLD',
        'WA' => 'WA',
        'SA' => 'SA',
        'TAS' => 'TAS',
        'NT' => 'NT',
        'AUSTRALIAN CAPITAL TERRITORY' => 'ACT',
        'NEW SOUTH WALES' => 'NSW',
        'VICTORIA' => 'VIC',
        'QUEENSLAND' => 'QLD',
        'WESTERN AUSTRALIA' => 'WA',
        'SOUTH AUSTRALIA' => 'SA',
        'TASMANIA' => 'TAS',
        'NORTHERN TERRITORY' => 'NT',
    ];

    public function parseSlug(?string $slug): ?array
    {
        $slug = Str::slug((string) $slug);

        if ($slug === '') {
            return null;
        }

        $segments = array_values(array_filter(explode('-', $slug), fn ($part) => $part !== ''));
        if ($segments === []) {
            return null;
        }

        $state = null;
        $stateCandidate = strtoupper((string) end($segments));
        if ($this->normalizeState($stateCandidate) !== null && count($segments) > 1) {
            $state = $this->normalizeState($stateCandidate);
            array_pop($segments);
        }

        if ($segments === []) {
            return null;
        }

        $suburbSlug = implode('-', $segments);
        $suburb = Str::title(str_replace('-', ' ', $suburbSlug));
        $canonicalSlug = $state !== null ? "{$suburbSlug}-".strtolower($state) : $suburbSlug;

        return [
            'suburb' => $suburb,
            'state' => $state,
            'slug' => $canonicalSlug,
            'location' => $state !== null ? "{$suburb}, {$state}" : $suburb,
        ];
    }

    public function fromSuburbAndState(string $suburb, ?string $state = null): ?array
    {
        $suburbSlug = Str::slug($suburb);

        if ($suburbSlug === '') {
            return null;
        }

        $stateAbbreviation = $this->normalizeState((string) $state);
        $slug = $stateAbbreviation !== null
            ? "{$suburbSlug}-".strtolower($stateAbbreviation)
            : $suburbSlug;

        $suburbLabel = Str::title(str_replace('-', ' ', $suburbSlug));

        return [
            'suburb' => $suburbLabel,
            'state' => $stateAbbreviation,
            'slug' => $slug,
            'location' => $stateAbbreviation !== null
                ? "{$suburbLabel}, {$stateAbbreviation}"
                : $suburbLabel,
        ];
    }

    public function fromLocationText(string $location, ?string $locationState = null): ?array
    {
        $location = trim($location);
        $locationState = trim((string) $locationState);

        if ($location === '') {
            return null;
        }

        if (str_contains($location, ',')) {
            [$suburb, $state] = array_map('trim', explode(',', $location, 2));

            return $this->fromSuburbAndState($suburb, $state);
        }

        return $this->fromSuburbAndState($location, $locationState !== '' ? $locationState : null);
    }

    public function normalizeState(string $state): ?string
    {
        $state = strtoupper(trim($state));

        if ($state === '') {
            return null;
        }

        return self::STATE_MAP[$state] ?? null;
    }
}
