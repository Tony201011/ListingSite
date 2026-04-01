<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BookableProviderUser implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $provider = User::query()
            ->whereKey($value)
            ->where('role', User::ROLE_PROVIDER)
            ->whereHas('providerProfile')
            ->exists();

        if (! $provider) {
            $fail('Booking enquiry must target a real provider listing.');
        }
    }
}
