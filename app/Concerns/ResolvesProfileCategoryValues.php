<?php

namespace App\Concerns;

use App\Models\Category;

trait ResolvesProfileCategoryValues
{
    /**
     * Resolve a stored category value (name, slug, or numeric ID) to the
     * category's display name. Returns null if no active matching category
     * is found under the given parent slug.
     */
    protected static function resolveProfileCategoryName(mixed $value, string $parentSlug): ?string
    {
        if (blank($value)) {
            return null;
        }

        $strValue = (string) $value;

        return Category::query()
            ->where('is_active', true)
            ->where('website_type', 'adult')
            ->whereHas('parent', fn ($q) => $q->where('slug', $parentSlug))
            ->where(function ($q) use ($strValue, $value): void {
                $q->where('name', $strValue)
                    ->orWhere('slug', $strValue);
                if (is_numeric($value)) {
                    $q->orWhereKey((int) $value);
                }
            })
            ->value('name');
    }

    /**
     * Resolve an array of stored category values (names, slugs, or numeric IDs)
     * to an array of display names using a single batched query.
     *
     * JSON-encoded arrays from Livewire/Filament state may arrive as strings
     * and are decoded before processing.
     */
    protected static function resolveProfileCategoryNames(mixed $state, string $parentSlug): array
    {
        // JSON-encoded arrays from Livewire/Filament state may arrive as strings
        if (is_string($state)) {
            $decoded = json_decode($state, true);
            $state = (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) ? $decoded : [];
        }

        if (! is_array($state)) {
            return [];
        }

        $values = collect($state)->flatten(1)->filter()->values()->all();

        if (empty($values)) {
            return [];
        }

        $numericIds = collect($values)->filter(fn ($v) => is_numeric($v))->map(fn ($v) => (int) $v)->all();
        $stringValues = collect($values)->filter(fn ($v) => ! is_numeric($v))->map(fn ($v) => (string) $v)->all();

        $categories = Category::query()
            ->where('is_active', true)
            ->where('website_type', 'adult')
            ->whereHas('parent', fn ($q) => $q->where('slug', $parentSlug))
            ->where(function ($q) use ($numericIds, $stringValues): void {
                $q->whereIn('name', $stringValues)
                    ->orWhereIn('slug', $stringValues);
                if (! empty($numericIds)) {
                    $q->orWhereIn('id', $numericIds);
                }
            })
            ->get(['id', 'name', 'slug']);

        $idMap = $categories->pluck('name', 'id')->all();
        $nameMap = $categories->pluck('name', 'name')->all();
        $slugMap = $categories->pluck('name', 'slug')->all();

        return collect($values)
            ->map(function ($val) use ($idMap, $nameMap, $slugMap) {
                $strVal = (string) $val;
                if (isset($nameMap[$strVal])) {
                    return $nameMap[$strVal];
                }
                if (is_numeric($val) && isset($idMap[(int) $val])) {
                    return $idMap[(int) $val];
                }

                return $slugMap[$strVal] ?? null;
            })
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
