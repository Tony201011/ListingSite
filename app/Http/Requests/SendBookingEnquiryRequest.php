<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendBookingEnquiryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['required', 'integer', 'exists:users,id'],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'datetime' => ['nullable', 'date', 'after_or_equal:today'],
            'services' => ['nullable', 'string', 'max:255'],
            'duration' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'message' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.required' => 'Profile is required.',
            'user_id.exists' => 'The selected profile does not exist.',
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'datetime.date' => 'Please enter a valid date.',
            'datetime.after_or_equal' => 'Booking date must be today or later.',
            'message.max' => 'Message may not be longer than 2000 characters.',
        ];
    }
}
