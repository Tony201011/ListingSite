<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;

class UploadVideosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'videos' => ['required', 'array', 'min:1'],
            'videos.*' => ['required', 'file', 'mimes:mp4,mov,avi,wmv,webm,mkv', 'max:102400'],
        ];
    }

    public function messages(): array
    {
        return [
            'videos.required' => 'Please upload at least one video.',
            'videos.array' => 'Videos must be sent as an array.',
            'videos.min' => 'Please upload at least one video.',
            'videos.*.required' => 'Each video file is required.',
            'videos.*.file' => 'Each uploaded item must be a valid file.',
            'videos.*.mimes' => 'Allowed video formats are mp4, mov, avi, wmv, webm, and mkv.',
            'videos.*.max' => 'Each video may not be greater than 100 MB.',
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
