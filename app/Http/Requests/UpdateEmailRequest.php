<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UpdateEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        /** @var \App\Models\User|null $user */
        $user = Auth::user();

        return [
            'current_password' => [
                'required',
                function ($attribute, $value, $fail) use ($user) {
                    if (! $user || ! Hash::check($value, $user->password)) {
                        $fail('The current password is incorrect.');
                    }
                },
            ],
            'new_email' => [
                'required',
                'email',
                Rule::unique('users', 'email')->ignore($user?->id),
                function ($attribute, $value, $fail) use ($user) {
                    if ($user && strtolower($value) === strtolower($user->email)) {
                        $fail('The new email must be different from your current email.');
                    }
                },
            ],
        ];
    }
}
