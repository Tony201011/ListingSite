<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Hash;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'password' => [
                'required',
                function ($attribute, $value, $fail) {
                    $user = $this->user();

                    if (! $user || ! Hash::check($value, $user->password)) {
                        $fail('The password you entered is incorrect.');
                    }
                },
            ],
            'confirmation_text' => ['required', 'in:DELETE'],
        ];
    }

    public function messages(): array
    {
        return [
            'password.required' => 'Password is required.',
            'confirmation_text.required' => 'Please type DELETE to confirm account deletion.',
            'confirmation_text.in' => 'You must type DELETE exactly to confirm account deletion.',
        ];
    }
}
