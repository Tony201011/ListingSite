<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEscortReviewRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'content' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'content.string' => 'Content must be a valid string.',
        ];
    }
}
