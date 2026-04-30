<?php

namespace App\Http\Requests;

use App\Models\GoogleRecaptchaSetting;
use App\Models\RecaptchaLog;
use App\Models\SiteSetting;
use App\ValueObjects\AustralianMobile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class ProviderSignupRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('mobile') && is_string($this->input('mobile'))) {
            try {
                $phone = AustralianMobile::fromString($this->input('mobile'));
                $this->merge(['mobile' => $phone->toLocal()]);
            } catch (\InvalidArgumentException) {
                // Leave as-is; the regex rule will reject it
            }
        }
    }

    public function rules(): array
    {
        $rules = [
            'email' => ['required', 'email', 'unique:users,email'],
            'nickname' => ['required', 'string', 'min:3', 'max:255'],
            'password' => ['required', 'min:8', 'confirmed'],
            'mobile' => ['required', 'regex:/^04\d{8}$/'],
            'suburb' => ['required', 'string', 'max:255'],
            'age_confirm' => ['accepted'],
            'referral_code' => ['nullable', 'string', 'max:255'],
            'account_user_referral_code' => ['nullable', 'string', 'max:255'],
        ];

        if ($this->shouldUseRecaptcha()) {
            $rules['g-recaptcha-response'] = ['required'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'mobile.regex' => 'The mobile number must be a valid Australian mobile starting with 04.',
            'age_confirm.accepted' => 'You must confirm your age.',
            'g-recaptcha-response.required' => 'Google reCAPTCHA verification failed. Please try again.',
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (! $this->shouldUseRecaptcha()) {
                return;
            }

            $recaptchaSetting = $this->getActiveRecaptchaSetting();
            $recaptcha = $this->input('g-recaptcha-response');
            $secret = $recaptchaSetting?->secret_key;

            if (! $recaptcha || ! $secret) {
                RecaptchaLog::create([
                    'action' => 'signup',
                    'ip_address' => $this->ip(),
                    'status' => 'failed',
                    'error_codes' => null,
                    'hostname' => null,
                ]);

                $validator->errors()->add(
                    'g-recaptcha-response',
                    'Google reCAPTCHA verification failed. Please try again.'
                );

                return;
            }

            try {
                $response = Http::asForm()
                    ->timeout(5)
                    ->retry(2, 200)
                    ->post('https://www.google.com/recaptcha/api/siteverify', [
                        'secret' => $secret,
                        'response' => $recaptcha,
                    ]);

                $result = $response->json();

                if (! $response->successful() || ! data_get($result, 'success', false)) {
                    RecaptchaLog::create([
                        'action' => 'signup',
                        'ip_address' => $this->ip(),
                        'status' => 'failed',
                        'error_codes' => data_get($result, 'error-codes'),
                        'hostname' => data_get($result, 'hostname'),
                    ]);

                    $validator->errors()->add(
                        'g-recaptcha-response',
                        'Google reCAPTCHA verification failed. Please try again.'
                    );
                } else {
                    RecaptchaLog::create([
                        'action' => 'signup',
                        'ip_address' => $this->ip(),
                        'status' => 'success',
                        'error_codes' => null,
                        'hostname' => data_get($result, 'hostname'),
                    ]);
                }
            } catch (\Throwable $e) {
                RecaptchaLog::create([
                    'action' => 'signup',
                    'ip_address' => $this->ip(),
                    'status' => 'failed',
                    'error_codes' => null,
                    'hostname' => null,
                ]);

                $validator->errors()->add(
                    'g-recaptcha-response',
                    'Google reCAPTCHA verification failed. Please try again.'
                );
            }
        });
    }

    private function getActiveRecaptchaSetting(): ?GoogleRecaptchaSetting
    {
        return GoogleRecaptchaSetting::where('is_active', 1)->first();
    }

    private function shouldUseRecaptcha(): bool
    {
        if (! $this->isCaptchaEnabledInSiteSettings()) {
            return false;
        }

        $recaptchaSetting = $this->getActiveRecaptchaSetting();

        return filled($recaptchaSetting?->site_key)
            && filled($recaptchaSetting?->secret_key);
    }

    private function isCaptchaEnabledInSiteSettings(): bool
    {
        if (! Schema::hasTable('site_settings')) {
            return true;
        }

        $siteSetting = SiteSetting::query()->latest('updated_at')->first();

        return $siteSetting?->captcha_enabled ?? true;
    }
}
