<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'provider_profile_id' => ['required', 'integer', 'exists:provider_profiles,id'],
            'reporter_name' => ['nullable', 'string', 'max:255'],
            'reporter_email' => ['nullable', 'email', 'max:255'],
            'reason' => ['required', 'string', 'in:spam,fake_profile,inappropriate_content,harassment,scam,other'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'provider_profile_id.required' => 'Profile is required.',
            'provider_profile_id.exists' => 'The reported profile does not exist.',
            'reason.required' => 'Please select a reason for reporting.',
            'reason.in' => 'Please select a valid reason.',
            'description.max' => 'Description may not be longer than 2000 characters.',
        ];
    }
}
