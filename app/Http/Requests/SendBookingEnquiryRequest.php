<?php

namespace App\Http\Requests;

use App\Rules\BookableProviderUser;
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
            'user_id' => ['required', 'integer', new BookableProviderUser],
            'name' => ['nullable', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'regex:/^[\d\s\-\+\(\)]{6,20}$/', 'max:20'],
            'datetime' => ['nullable', 'date', 'after:2024-12-31'],
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
            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'phone.regex' => 'Please enter a valid phone number.',
            'datetime.date' => 'Please enter a valid date.',
            'datetime.after' => 'Booking date must be after December 31, 2024.',
            'message.max' => 'Message may not be longer than 2000 characters.',
        ];
    }
}
