<?php

namespace App\Services;

use Twilio\Rest\Client;

class TwilioService
{
    protected $client;
    protected $from;

    public function __construct()
    {
        $this->client = new Client(config('twilio.sid'), config('twilio.auth_token'));
        $this->from = config('twilio.phone_number');
    }

    /**
     * Send an SMS message.
     *
     * @param string $to   Recipient phone number (E.164 format)
     * @param string $message
     * @return \Twilio\Rest\Api\V2010\Account\MessageInstance
     */
    public function sendSms($to, $message)
    {
        return $this->client->messages->create($to, [
            'from' => $this->from,
            'body' => $message,
        ]);
    }
}
