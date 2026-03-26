<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchTourCityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'min:2', 'max:255'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'q' => is_string($this->get('q')) ? trim($this->get('q')) : $this->get('q'),
        ]);
    }

    public function messages(): array
    {
        return [
            'q.string' => 'Search query must be a valid string.',
            'q.min' => 'Search query must be at least 2 characters.',
            'q.max' => 'Search query may not be greater than 255 characters.',
        ];
    }
}
