<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeleteAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'confirmation_text' => ['required', 'in:DELETE'],
        ];
    }

    public function messages(): array
    {
        return [
            'confirmation_text.required' => 'Please type DELETE to confirm account deletion.',
            'confirmation_text.in' => 'You must type DELETE exactly to confirm account deletion.',
        ];
    }
}
