<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutPurchaseCreditRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'credits' => ['required', 'integer', Rule::in([7, 30, 60, 120, 180])],
            'invoice_name' => ['required', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'credits.required' => 'Please select a credit package.',
            'credits.integer' => 'Credits must be a valid number.',
            'credits.in' => 'Selected credit package is invalid.',
            'invoice_name.required' => 'Invoice name is required.',
            'invoice_name.string' => 'Invoice name must be a valid string.',
            'invoice_name.max' => 'Invoice name may not be greater than 120 characters.',
        ];
    }
}
