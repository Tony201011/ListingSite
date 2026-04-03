<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class HomeIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'categories' => is_array($this->input('categories')) ? $this->input('categories') : [],
            'min_age' => $this->input('min_age', 18),
            'max_age' => $this->input('max_age', 40),
            'min_price' => $this->input('min_price', 150),
            'max_price' => $this->input('max_price', 400),
            'location' => trim((string) $this->input('location', '')),
            'escort_name' => trim((string) $this->input('escort_name', '')),
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
            'min_age' => ['nullable', 'integer', 'min:18', 'max:100'],
            'max_age' => ['nullable', 'integer', 'min:18', 'max:100'],
            'min_price' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'max_price' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'location' => ['nullable', 'string', 'max:255'],
            'escort_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'categories.array' => 'Categories must be a valid array.',
            'categories.*.integer' => 'Each category must be a valid ID.',
            'categories.*.exists' => 'One or more selected categories are invalid.',
            'min_age.integer' => 'Minimum age must be a valid number.',
            'max_age.integer' => 'Maximum age must be a valid number.',
            'min_price.integer' => 'Minimum price must be a valid number.',
            'max_price.integer' => 'Maximum price must be a valid number.',
        ];
    }
}
