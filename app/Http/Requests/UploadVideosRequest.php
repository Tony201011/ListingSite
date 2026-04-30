<?php

namespace App\Http\Requests;

use App\Models\SiteSetting;
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
        $maxKb = SiteSetting::getMaxVideoUploadMb() * 1024;

        return [
            'videos' => ['required', 'array', 'min:1', 'max:5'],
            'videos.*' => ['required', 'file', 'mimes:mp4,mov,avi,wmv,webm,mkv', "max:{$maxKb}"],
        ];
    }

    public function messages(): array
    {
        $maxMb = SiteSetting::getMaxVideoUploadMb();

        return [
            'videos.required' => 'Please upload at least one video.',
            'videos.array' => 'Videos must be sent as an array.',
            'videos.min' => 'Please upload at least one video.',
            'videos.max' => 'You may not upload more than 5 videos at a time.',
            'videos.*.required' => 'Each video file is required.',
            'videos.*.file' => 'Each uploaded item must be a valid file.',
            'videos.*.mimes' => 'Allowed video formats are mp4, mov, avi, wmv, webm, and mkv.',
            'videos.*.max' => "Each video may not be greater than {$maxMb} MB.",
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
