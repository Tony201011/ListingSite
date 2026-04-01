<?php

namespace App\Rules;

use App\Models\Category;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CategoryOfType implements ValidationRule
{
    public function __construct(
        private string $parentSlug,
        private string $websiteType = 'adult',
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $exists = Category::query()
            ->where('id', $value)
            ->where('is_active', true)
            ->where('website_type', $this->websiteType)
            ->whereHas('parent', fn ($q) => $q->where('slug', $this->parentSlug))
            ->exists();

        if (! $exists) {
            $label = str_replace(['-', '_'], ' ', $this->parentSlug);
            $fail("The selected :attribute is not a valid {$label} option.");
        }
    }
}
