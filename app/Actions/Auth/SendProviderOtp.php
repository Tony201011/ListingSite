<?php

namespace App\Actions\Auth;

use App\Models\TwilioSetting;
use App\ValueObjects\AustralianMobile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;
use Twilio\Http\CurlClient;
use Twilio\Rest\Client;

class SendProviderOtp
{
    public function execute(string $mobile): array
    {
        try {
            $phone = AustralianMobile::fromString($mobile);
        } catch (InvalidArgumentException) {
            return [
                'success' => false,
                'message' => 'Invalid Australian mobile number.',
            ];
        }

        $twilioSetting = TwilioSetting::query()->first();
        $otp = random_int(100000, 999999);
        $otpExpiresAt = now()->addMinutes(2);

        if ($this->isDummyMobile($phone, $twilioSetting)) {
            $otp = (int) ($twilioSetting->dummy_otp ?: $otp);

            Log::info('Dummy mobile OTP mode used', [
                'mobile' => $phone->toMasked(),
            ]);

            return [
                'success' => true,
                'otp_hash' => Hash::make((string) $otp),
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
                'mobile' => $phone->toMasked(),
            ]);

            return [
                'success' => false,
                'message' => 'SMS service is not configured properly.',
            ];
        }

        try {
            $httpClient = new CurlClient([
                CURLOPT_TIMEOUT => 10,
                CURLOPT_CONNECTTIMEOUT => 5,
            ]);

            $client = new Client(
                $twilioSetting->api_sid,
                $twilioSetting->api_secret,
                $twilioSetting->account_sid,
                null,
                $httpClient
            );

            $client->messages->create(
                $phone->toE164(),
                [
                    'from' => $twilioSetting->phone_number,
                    'body' => "Your HOTESCORT verification code is: {$otp}",
                ]
            );

            Log::info('OTP SMS sent', [
                'mobile' => $phone->toMasked(),
            ]);

            return [
                'success' => true,
                'otp_hash' => Hash::make((string) $otp),
                'expires_at' => $otpExpiresAt,
            ];
        } catch (\Exception $e) {
            Log::error('Twilio SMS error: ' . $e->getMessage(), [
                'mobile' => $phone->toMasked(),
            ]);

            return [
                'success' => false,
                'message' => 'Failed to send OTP. Please try again.',
            ];
        }
    }

    private function isDummyMobile(AustralianMobile $phone, ?TwilioSetting $twilioSetting): bool
    {
        if (! $twilioSetting || ! $twilioSetting->dummy_mode_enabled) {
            return false;
        }

        if (blank($twilioSetting->dummy_mobile_number) || blank($twilioSetting->dummy_otp)) {
            return false;
        }

        return $phone->equals($twilioSetting->dummy_mobile_number);
    }
}
