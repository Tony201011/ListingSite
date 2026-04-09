<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
use App\Models\SmsLog;
use App\Services\TwilioService;

class SendSms
{
    public function __construct(
        private TwilioService $twilio
    ) {}

    public function execute(string $phone, string $message): ActionResult
    {
        try {
            $response = $this->twilio->sendSms($phone, $message);

            SmsLog::create([
                'recipient' => $phone,
                'message' => $message,
                'status' => 'sent',
                'sid' => $response->sid ?? null,
                'sent_at' => now(),
            ]);

            return ActionResult::success([
                'sid' => $response->sid,
            ], 'SMS sent successfully.');
        } catch (\Throwable $e) {
            SmsLog::create([
                'recipient' => $phone,
                'message' => $message,
                'status' => 'failed',
                'error' => $e->getMessage(),
                'sent_at' => now(),
            ]);

            return ActionResult::infrastructureFailure(
                config('app.debug')
                    ? $e->getMessage()
                    : 'Failed to send SMS.'
            );
        }
    }
}
