<?php

namespace App\Http\Controllers;

use App\Actions\SendSms;
use App\Http\Requests\SendSmsRequest;
use Illuminate\Http\JsonResponse;

class SmsController extends Controller
{
    public function __construct(
        private SendSms $sendSms
    ) {
    }

    public function send(SendSmsRequest $request): JsonResponse
    {
        $result = $this->sendSms->execute(
            $request->validated('phone'),
            $request->validated('message')
        );

        return response()->json($result['data'], $result['status']);
    }
}
