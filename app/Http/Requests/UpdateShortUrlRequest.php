<?php

namespace App\Http\Requests;

use App\Actions\GetActiveProviderProfile;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class UpdateShortUrlRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        $user = Auth::user();
        $profile = $user ? app(GetActiveProviderProfile::class)->execute($user) : null;
        $profileId = $profile?->id;

        return [
            'slug' => [
                'required',
                'alpha_dash',
                Rule::unique('short_urls', 'short_url')->ignore($profileId, 'provider_profile_id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'slug.required' => 'Slug is required.',
            'slug.alpha_dash' => 'Slug may only contain letters, numbers, dashes, and underscores.',
            'slug.unique' => 'This short URL is already taken.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
