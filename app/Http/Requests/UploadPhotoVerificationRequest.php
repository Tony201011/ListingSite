<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class UploadPhotoVerificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'photos' => ['required', 'array', 'min:2', 'max:5'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    public function messages(): array
    {
        return [
            'photos.required' => 'Please upload your verification photos.',
            'photos.array' => 'Photos must be sent as an array.',
            'photos.min' => 'Please upload at least two verification photos.',
            'photos.max' => 'You may not upload more than 5 verification photos at a time.',
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
