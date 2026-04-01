<?php

namespace App\Actions;

use App\Actions\Support\ActionResult;
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

            return ActionResult::success([
                'sid' => $response->sid,
            ], 'SMS sent successfully.');
        } catch (\Throwable $e) {
            return ActionResult::infrastructureFailure(
                config('app.debug')
                    ? $e->getMessage()
                    : 'Failed to send SMS.'
            );
        }
    }
}
