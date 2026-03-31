<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class UpdateRateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'description' => ['required', 'string', 'max:255'],
            'incall' => ['required', 'numeric', 'min:0', 'max:99999'],
            'outcall' => ['required', 'numeric', 'min:0', 'max:99999'],
            'extra' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'description.required' => 'Description is required.',
            'description.max' => 'Description may not be greater than 255 characters.',
            'incall.required' => 'Incall rate is required.',
            'incall.numeric' => 'Incall rate must be a number.',
            'incall.min' => 'Incall rate must be at least 0.',
            'incall.max' => 'Incall rate may not be greater than 99999.',
            'outcall.required' => 'Outcall rate is required.',
            'outcall.numeric' => 'Outcall rate must be a number.',
            'outcall.min' => 'Outcall rate must be at least 0.',
            'outcall.max' => 'Outcall rate may not be greater than 99999.',
        ];
    }
}
