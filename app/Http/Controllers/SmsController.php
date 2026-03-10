<?php

namespace App\Http\Controllers;

use App\Services\TwilioService;
use Illuminate\Http\Request;

class SmsController extends Controller
{
    protected $twilio;

    public function __construct(TwilioService $twilio)
    {
        $this->twilio = $twilio;
    }

    public function send(Request $request)
    {
        // $request->validate([
        //     'phone' => 'required|string',
        //     'message' => 'required|string',
        // ]);

        $phone =' +919988380772';
        $message = 'Hello from twillow test message!';

        try {
            $message = $this->twilio->sendSms($phone, $message);
            return response()->json([
                'success' => true,
                'sid' => $message->sid,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
