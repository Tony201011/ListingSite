<?php

namespace App\Http\Requests;

use App\Rules\CategoryOfType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class SaveMyProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'suburb' => ['required', 'string', 'max:255'],
            'introduction_line' => ['required', 'string', 'max:1000'],
            'profile_text' => ['required', 'string', 'max:15000'],

            'age_group' => ['required', 'integer', new CategoryOfType('age-group')],
            'hair_color' => ['required', 'integer', new CategoryOfType('hair-color')],
            'hair_length' => ['required', 'integer', new CategoryOfType('hair-length')],
            'ethnicity' => ['required', 'integer', new CategoryOfType('ethnicity')],
            'body_type' => ['required', 'integer', new CategoryOfType('body-type')],
            'bust_size' => ['required', 'integer', new CategoryOfType('bust-size')],
            'your_length' => ['required', 'integer', new CategoryOfType('your-length')],

            'availability' => ['required', 'string', 'max:100'],
            'contact_method' => ['required', 'string', 'max:100'],
            'phone_contact' => ['required', 'string', 'max:100'],
            'time_waster' => ['required', 'string', 'max:100'],

            'primary_identity' => ['required', 'array', 'min:1'],
            'primary_identity.*' => ['string'],

            'attributes' => ['required', 'array', 'min:1'],
            'attributes.*' => ['string'],

            'services_style' => ['required', 'array', 'min:1', 'max:12'],
            'services_style.*' => ['string'],

            'services_provided' => ['required', 'array', 'min:1'],
            'services_provided.*' => ['string'],

            'twitter_handle' => ['nullable', 'string', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            'onlyfans_username' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'suburb.required' => 'Suburb is required.',
            'introduction_line.required' => 'Introduction line is required.',
            'profile_text.required' => 'Profile text is required.',

            'age_group.required' => 'Age group is required.',
            'age_group.exists' => 'Selected age group is invalid.',

            'hair_color.required' => 'Hair color is required.',
            'hair_color.exists' => 'Selected hair color is invalid.',

            'hair_length.required' => 'Hair length is required.',
            'hair_length.exists' => 'Selected hair length is invalid.',

            'ethnicity.required' => 'Ethnicity is required.',
            'ethnicity.exists' => 'Selected ethnicity is invalid.',

            'body_type.required' => 'Body type is required.',
            'body_type.exists' => 'Selected body type is invalid.',

            'bust_size.required' => 'Bust size is required.',
            'bust_size.exists' => 'Selected bust size is invalid.',

            'your_length.required' => 'Your length is required.',
            'your_length.exists' => 'Selected your length is invalid.',

            'availability.required' => 'Availability is required.',
            'contact_method.required' => 'Contact method is required.',
            'phone_contact.required' => 'Phone contact preference is required.',
            'time_waster.required' => 'Time waster shield is required.',

            'primary_identity.required' => 'Please select at least one primary identity option.',
            'primary_identity.array' => 'Primary identity must be a valid list.',
            'primary_identity.min' => 'Please select at least one primary identity option.',

            'attributes.required' => 'Please select at least one attribute.',
            'attributes.array' => 'Attributes must be a valid list.',
            'attributes.min' => 'Please select at least one attribute.',

            'services_style.required' => 'Please select at least one services style option.',
            'services_style.array' => 'Services style must be a valid list.',
            'services_style.min' => 'Please select at least one services style option.',
            'services_style.max' => 'You may not select more than 12 services style options.',

            'services_provided.required' => 'Please select at least one service provided.',
            'services_provided.array' => 'Services provided must be a valid list.',
            'services_provided.min' => 'Please select at least one service provided.',

            'website.url' => 'Website must be a valid URL.',
        ];
    }
}
