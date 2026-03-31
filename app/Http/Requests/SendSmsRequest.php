<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class SendSmsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // or Auth::check() if protected route
    }

    public function rules(): array
    {
        return [
            'phone' => ['required', 'string', 'regex:/^[\d\s\-\+\(\)]{6,20}$/'],
            'message' => ['required', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => 'Phone number is required.',
            'message.required' => 'Message is required.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
