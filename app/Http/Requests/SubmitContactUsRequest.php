<?php

namespace App\Http\Requests;

use App\Models\ContactUsPage;
use Illuminate\Foundation\Http\FormRequest;

class SubmitContactUsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $contactPage = ContactUsPage::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        $enableName = $contactPage?->enable_name_field ?? true;
        $enableEmail = $contactPage?->enable_email_field ?? true;
        $enableSubject = $contactPage?->enable_subject_field ?? true;
        $enableMessage = $contactPage?->enable_message_field ?? true;

        $rules = [];

        if ($enableName) {
            $rules['name'] = ['required', 'string', 'max:100'];
        }

        if ($enableEmail) {
            $rules['email'] = ['required', 'email', 'max:190'];
        }

        if ($enableSubject) {
            $rules['subject'] = ['required', 'string', 'max:190'];
        }

        if ($enableMessage) {
            $rules['message'] = ['required', 'string', 'max:3000'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Name is required.',
            'name.string' => 'Name must be a valid string.',
            'name.max' => 'Name may not be greater than 100 characters.',

            'email.required' => 'Email is required.',
            'email.email' => 'Please enter a valid email address.',
            'email.max' => 'Email may not be greater than 190 characters.',

            'subject.required' => 'Subject is required.',
            'subject.string' => 'Subject must be a valid string.',
            'subject.max' => 'Subject may not be greater than 190 characters.',

            'message.required' => 'Message is required.',
            'message.string' => 'Message must be a valid string.',
            'message.max' => 'Message may not be greater than 3000 characters.',
        ];
    }
}
