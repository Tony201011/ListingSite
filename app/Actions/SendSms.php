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
        $status = 'failed';
        $sid = null;
        $error = null;
        $result = null;

        try {
            $response = $this->twilio->sendSms($phone, $message);
            $status = 'sent';
            $sid = $response->sid ?? null;
            $result = ActionResult::success([
                'sid' => $response->sid,
            ], 'SMS sent successfully.');
        } catch (\Throwable $e) {
            $error = $e->getMessage();
            $result = ActionResult::infrastructureFailure(
                config('app.debug')
                    ? $e->getMessage()
                    : 'Failed to send SMS.'
            );
        } finally {
            try {
                SmsLog::create([
                    'recipient' => $phone,
                    'message' => $message,
                    'status' => $status,
                    'sid' => $sid,
                    'error' => $error,
                    'sent_at' => now(),
                ]);
            } catch (\Throwable $logException) {
                report($logException);
            }
        }

        return $result;
    }
}
