<?php

namespace App\Http\Requests;

use App\Models\CreditPackage;
use App\Models\ProviderProfile;
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

        if (! $this->has('provider_profile_id')) {
            $fallbackProfileId = session('active_provider_profile_id')
                ?: $this->user()?->providerProfiles()->orderBy('id')->value('id');

            $this->merge([
                'provider_profile_id' => $fallbackProfileId,
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
                Rule::exists(CreditPackage::class, 'id')->where(
                    fn ($query) => $query->where(function ($builder): void {
                        $builder->where('is_active', true)
                            ->orWhere(function ($legacyBuilder): void {
                                $legacyBuilder->whereNull('is_active')
                                    ->where('status', 'active');
                            });
                    })
                ),
            ],
            'invoice_name' => ['required', 'string', 'max:120'],
            'provider_profile_id' => [
                'required',
                'integer',
                Rule::exists(ProviderProfile::class, 'id')->where(
                    fn ($query) => $query->where('user_id', $this->user()?->id)
                ),
            ],
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
            'provider_profile_id.required' => 'Please select a provider profile for this purchase.',
            'provider_profile_id.exists' => 'The selected provider profile is invalid.',
        ];
    }
}
