<?php

namespace App\Http\Requests;

use App\Models\ListingContentReport;
use Illuminate\Foundation\Http\FormRequest;

class StoreListingContentReportRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category' => ['required', 'string', 'in:'.implode(',', array_keys(ListingContentReport::categoryOptions()))],
            'listing_url' => ['required', 'url', 'max:2048'],
            'advertiser_name' => ['required', 'string', 'max:255'],
            'listing_id' => ['nullable', 'string', 'max:255'],
            'listing_phone' => ['nullable', 'string', 'max:50'],
            'listing_location' => ['nullable', 'string', 'max:255'],
            'reporter_name' => ['nullable', 'string', 'max:255'],
            'reporter_email' => ['required', 'email', 'max:255'],
            'reporter_phone' => ['nullable', 'string', 'max:50'],
            'is_anonymous' => ['sometimes', 'boolean'],
            'description' => ['required', 'string', 'max:5000'],
            'evidence' => ['nullable', 'array'],
            'evidence.*' => ['file', 'mimes:jpg,jpeg,png,pdf', 'max:10240'],
            'is_urgent' => ['sometimes', 'boolean'],
            'is_person_shown' => ['sometimes', 'boolean'],
            'declaration_accuracy' => ['accepted'],
            'declaration_contact' => ['accepted'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_anonymous' => $this->boolean('is_anonymous'),
            'is_urgent' => $this->boolean('is_urgent'),
            'is_person_shown' => $this->boolean('is_person_shown'),
        ]);
    }
}
