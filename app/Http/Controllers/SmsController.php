<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendSmsRequest;
use App\Services\TwilioService;
use Illuminate\Http\JsonResponse;

class SmsController extends Controller
{
    protected TwilioService $twilio;

    public function __construct(TwilioService $twilio)
    {
        $this->twilio = $twilio;
    }

    public function send(SendSmsRequest $request): JsonResponse
    {
        $phone = $request->validated('phone');
        $text = $request->validated('message');

        try {
            $response = $this->twilio->sendSms($phone, $text);

            return response()->json([
                'success' => true,
                'sid' => $response->sid,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'error' => config('app.debug')
                    ? $e->getMessage()
                    : 'Failed to send SMS.',
            ], 500);
        }
    }
}
