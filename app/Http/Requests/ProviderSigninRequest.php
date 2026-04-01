<?php

namespace App\Http\Requests;

use App\Models\GoogleRecaptchaSetting;
use App\Models\SiteSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class ProviderSigninRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = [
            'email' => ['required', 'email'],
            'password' => ['required'],
        ];

        if ($this->shouldUseRecaptcha()) {
            $rules['g-recaptcha-response'] = ['required'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'g-recaptcha-response.required' => 'reCAPTCHA verification failed',
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
                $validator->errors()->add(
                    'g-recaptcha-response',
                    'reCAPTCHA verification failed'
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
                    $validator->errors()->add(
                        'g-recaptcha-response',
                        'reCAPTCHA verification failed'
                    );
                }
            } catch (\Throwable $e) {
                $validator->errors()->add(
                    'g-recaptcha-response',
                    'reCAPTCHA verification failed'
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
