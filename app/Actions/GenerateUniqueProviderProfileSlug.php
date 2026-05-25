<?php

namespace App\Actions;

use App\Models\ProviderProfile;
use Illuminate\Support\Str;

class GenerateUniqueProviderProfileSlug
{
    public function execute(string $name, ?int $excludeProfileId = null): string
    {
        $base = Str::slug($name) ?: 'profile';
        $slug = $base;
        $counter = 1;

        while (
            ProviderProfile::where('slug', $slug)
                ->when($excludeProfileId !== null, fn ($q) => $q->where('id', '!=', $excludeProfileId))
                ->exists()
        ) {
            $slug = $base.'-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
