<?php

namespace App\Concerns;

use Illuminate\Support\Collection;

trait ResolvesProfileCategoryIds
{
    /**
     * Resolve an array of category IDs (or plain name strings) to an array of
     * human-readable category names using a pre-fetched ID → name map.
     *
     * Non-numeric strings are treated as already-resolved names and are kept as-is.
     * Integer IDs that have no matching entry in $categoryNames are silently dropped.
     */
    protected function resolveIds(array $values, Collection $categoryNames): array
    {
        $resolved = [];

        foreach ($values as $value) {
            if (is_numeric($value)) {
                $name = $categoryNames->get((int) $value);
                if ($name !== null) {
                    $resolved[] = $name;
                }
            } elseif (is_string($value) && trim($value) !== '') {
                $resolved[] = trim($value);
            }
        }

        return array_values(array_unique($resolved));
    }

    /**
     * Extract just the suburb/locality name from a raw suburb string.
     *
     * The users.suburb column stores values in the form "SuburbName, STATE postcode"
     * (e.g. "Melbourne, VIC 3000"). This helper strips the state and postcode portion
     * so only the clean suburb name is returned for display purposes.
     */
    protected function extractSuburbName(string $suburb): string
    {
        if ($suburb === '') {
            return '';
        }

        return str_contains($suburb, ',') ? trim(explode(',', $suburb, 2)[0]) : $suburb;
    }
}
