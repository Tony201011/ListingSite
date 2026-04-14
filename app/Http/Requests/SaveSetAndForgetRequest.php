<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSetAndForgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

        return [
            'online_now_enabled' => ['boolean'],
            'online_now_days' => ['nullable', 'array'],
            'online_now_days.*' => [Rule::in($days)],
            'online_now_time' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
            'available_now_enabled' => ['boolean'],
            'available_now_days' => ['nullable', 'array'],
            'available_now_days.*' => [Rule::in($days)],
            'available_now_time' => ['nullable', 'regex:/^\d{2}:\d{2}$/'],
        ];
    }
}
