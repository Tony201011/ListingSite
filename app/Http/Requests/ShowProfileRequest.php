<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ShowProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'categories' => is_array($this->input('categories')) ? $this->input('categories') : [],
        ]);
    }

    public function rules(): array
    {
        return [
            'categories' => ['nullable', 'array'],
            'categories.*' => [
                'integer',
                Rule::exists('categories', 'id')->where('is_active', true),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'categories.array' => 'Categories must be a valid array.',
            'categories.*.integer' => 'Each category must be a valid ID.',
            'categories.*.exists' => 'One or more selected categories are invalid.',
        ];
    }
}
