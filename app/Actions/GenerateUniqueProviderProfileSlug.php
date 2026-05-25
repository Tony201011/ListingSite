<?php

namespace App\Actions;

use Illuminate\Support\Str;

/**
 * Generate the base slug for a provider profile name.
 *
 * The slug is derived from the name using standard URL-friendly rules
 * (lowercase, hyphens for spaces, special characters removed).  Uniqueness
 * is guaranteed separately via the `profile_sequence` column rather than
 * by appending a counter to the slug.
 */
class GenerateUniqueProviderProfileSlug
{
    public function execute(string $name): string
    {
        return Str::slug($name) ?: 'profile';
    }
}
