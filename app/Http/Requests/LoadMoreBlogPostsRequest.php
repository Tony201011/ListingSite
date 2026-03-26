<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoadMoreBlogPostsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'page' => $this->query('page', 1),
        ]);
    }

    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'page.integer' => 'The page must be a valid integer.',
            'page.min' => 'The page must be at least 1.',
        ];
    }
}
