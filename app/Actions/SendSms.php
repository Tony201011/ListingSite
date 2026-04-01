<?php

namespace App\Actions;

use App\Services\TwilioService;

class SendSms
{
    public function __construct(
        private TwilioService $twilio
    ) {}

    public function execute(string $phone, string $message): array
    {
        try {
            $response = $this->twilio->sendSms($phone, $message);

            return [
                'status' => 200,
                'data' => [
                    'success' => true,
                    'sid' => $response->sid,
                ],
            ];
        } catch (\Throwable $e) {
            return [
                'status' => 500,
                'data' => [
                    'success' => false,
                    'error' => config('app.debug')
                        ? $e->getMessage()
                        : 'Failed to send SMS.',
                ],
            ];
        }
    }
}
