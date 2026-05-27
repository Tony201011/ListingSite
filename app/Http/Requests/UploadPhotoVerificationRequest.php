<?php

namespace App\Http\Requests;

use App\Actions\GetActiveProviderProfile;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class UploadPhotoVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check() && Auth::user()->providerProfile()->exists();
    }

    public function rules(): array
    {
        $user = Auth::user();
        $profile = $user ? app(GetActiveProviderProfile::class)->execute($user) : null;
        $existingVerification = $profile?->photoVerification()
            ->whereNull('deleted_at')
            ->latest('created_at')
            ->first();

        $existingPhotoCount = count(Arr::wrap($existingVerification?->photos));
        $remainingSlots = max(0, 2 - $existingPhotoCount);
        $minimumPhotos = $existingPhotoCount > 0 ? $remainingSlots : 2;

        return [
            'photos' => ['required', 'array', 'min:'.$minimumPhotos, 'max:'.$remainingSlots],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'photos.required' => 'Please upload your verification photos.',
            'photos.array' => 'Photos must be sent as an array.',
            'photos.min' => 'Please upload the required verification photo count.',
            'photos.max' => 'You may not upload more verification photos than the remaining empty slots.',
            'photos.*.required' => 'Each verification photo is required.',
            'photos.*.image' => 'Each uploaded item must be a valid image.',
            'photos.*.mimes' => 'Allowed image formats are jpg, jpeg, png, and webp.',
            'photos.*.max' => 'Each photo may not be greater than 10 MB.',
        ];
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Validation failed.',
            'errors' => $validator->errors(),
        ], 422));
    }
}
