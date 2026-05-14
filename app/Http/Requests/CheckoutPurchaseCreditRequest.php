<?php

namespace App\Http\Requests;

use App\Models\CreditPackage;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CheckoutPurchaseCreditRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $lockedPackageId = $this->session()->get('purchase_credit_locked_package_id');

        if ($lockedPackageId) {
            $this->merge([
                'package_id' => $lockedPackageId,
            ]);
        }
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'package_id' => [
                'required',
                'integer',
                Rule::exists(CreditPackage::class, 'id')->where('status', 'active'),
            ],
            'invoice_name' => ['required', 'string', 'max:120'],
        ];
    }

    public function messages(): array
    {
        return [
            'package_id.required' => 'Please select a credit package.',
            'package_id.integer' => 'The selected package is invalid.',
            'package_id.exists' => 'The selected credit package is no longer available.',
            'invoice_name.required' => 'Invoice name is required.',
            'invoice_name.string' => 'Invoice name must be a valid string.',
            'invoice_name.max' => 'Invoice name may not be greater than 120 characters.',
        ];
    }
}
