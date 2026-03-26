<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreTourRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'city' => ['required', 'string', 'max:255'],
            'from' => ['required', 'date', 'after_or_equal:now'],
            'to' => ['required', 'date', 'after:from'],
            'description' => ['nullable', 'string'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'enabled' => $this->boolean('enabled'),
        ]);
    }

    public function messages(): array
    {
        return [
            'city.required' => 'City is required.',
            'city.max' => 'City may not be greater than 255 characters.',
            'from.required' => 'From date is required.',
            'from.date' => 'From must be a valid date.',
            'from.after_or_equal' => 'From date must be today or later.',
            'to.required' => 'To date is required.',
            'to.date' => 'To must be a valid date.',
            'to.after' => 'To date must be after the from date.',
        ];
    }
}
