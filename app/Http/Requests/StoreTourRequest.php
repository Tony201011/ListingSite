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
            'from' => ['required', 'date', 'after_or_equal:today'],
            'to' => ['required', 'date'],
            'description' => ['nullable', 'string'],
            'enabled' => ['sometimes', 'boolean'],
        ];
    }

    public function withValidator(\Illuminate\Validation\Validator $validator): void
    {
        $validator->after(function (\Illuminate\Validation\Validator $v) {
            $from = strtotime((string) $this->input('from'));
            $to = strtotime((string) $this->input('to'));

            if ($from !== false && $to !== false && $to <= $from) {
                $v->errors()->add('to', 'To date must be after the from date.');
            }
        });
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
