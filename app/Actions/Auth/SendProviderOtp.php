<?php

namespace App\Actions\Auth;
use App\Models\TwilioSetting;
use Illuminate\Support\Facades\Log;
use Twilio\Rest\Client;
class SendProviderOtp
{
    public function execute(string $mobile): array
    {
        $twilioSetting = TwilioSetting::query()->first();
        $otp = random_int(100000, 999999);
        $otpExpiresAt = now()->addMinutes(2);

        if ($this->isDummyMobile($mobile, $twilioSetting)) {
            $otp = (int) ($twilioSetting->dummy_otp ?: $otp);

            Log::info('Dummy mobile OTP mode used', [
                'mobile' => $this->maskMobile($mobile),
            ]);

            return [
                'success' => true,
                'otp' => $otp,
                'expires_at' => $otpExpiresAt,
            ];
        }

        if (
            ! $twilioSetting ||
            empty($twilioSetting->api_sid) ||
            empty($twilioSetting->api_secret) ||
            empty($twilioSetting->account_sid) ||
            empty($twilioSetting->phone_number)
        ) {
            Log::error('Twilio configuration missing for OTP send.', [
                'mobile' => $this->maskMobile($mobile),
            ]);

            return [
                'success' => false,
                'message' => 'SMS service is not configured properly.',
            ];
        }

        try {
            $client = new Client(
                $twilioSetting->api_sid,
                $twilioSetting->api_secret,
                $twilioSetting->account_sid
            );

            $client->messages->create(
                $this->convertToTwilioE164($mobile),
                [
                    'from' => $twilioSetting->phone_number,
                    'body' => "Your HOTESCORT verification code is: {$otp}",
                ]
            );

            Log::info('Twilio SMS send attempt', [
                'mobile' => $this->maskMobile($mobile),
            ]);

            return [
                'success' => true,
                'otp' => $otp,
                'expires_at' => $otpExpiresAt,
            ];
        } catch (\Exception $e) {
            Log::error('Twilio SMS error: ' . $e->getMessage(), [
                'mobile' => $this->maskMobile($mobile),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ];
        }
    }

    private function isDummyMobile(?string $mobile, ?TwilioSetting $twilioSetting): bool
    {
        if (! $twilioSetting || ! $twilioSetting->dummy_mode_enabled) {
            return false;
        }

        if (blank($twilioSetting->dummy_mobile_number) || blank($twilioSetting->dummy_otp)) {
            return false;
        }

        return trim((string) $mobile) === trim((string) $twilioSetting->dummy_mobile_number);
    }

    private function maskMobile(string $mobile): string
    {
        $length = strlen($mobile);

        if ($length <= 4) {
            return str_repeat('*', $length);
        }

        return str_repeat('*', $length - 4) . substr($mobile, -4);
    }

    private function convertToTwilioE164(string $mobile): string
    {
        return preg_replace('/^0/', '+61', $mobile);
    }
}
