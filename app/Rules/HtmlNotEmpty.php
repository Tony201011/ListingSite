<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class HtmlNotEmpty implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $stripped = trim(strip_tags((string) $value));
        // Remove non-breaking spaces and other whitespace-only content
        $stripped = preg_replace('/[\x{00A0}\s]+/u', '', $stripped);

        if ($stripped === '') {
            $fail('The :attribute field is required and must contain text.');
        }
    }
}
