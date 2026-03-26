<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'incall' => ['required', 'string', 'max:50'],
            'outcall' => ['required', 'string', 'max:50'],
            'extra' => ['nullable', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'Description is required.',
            'description.max' => 'Description may not be greater than 255 characters.',
            'incall.required' => 'Incall is required.',
            'incall.max' => 'Incall may not be greater than 50 characters.',
            'outcall.required' => 'Outcall is required.',
            'outcall.max' => 'Outcall may not be greater than 50 characters.',
        ];
    }
}
